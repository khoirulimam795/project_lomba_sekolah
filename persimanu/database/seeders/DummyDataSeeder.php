<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Kontingen;
use App\Models\KriteriaKomponen;
use App\Models\Lomba;
use App\Models\LombaKontingen;
use App\Models\Penilaian;
use App\Models\PenilaianDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Guard: cegah double-seed (kalau mau reset, pakai migrate:fresh --seed)
        if (User::where('email', 'juri1@dummy.test')->exists()) {
            $this->command?->warn('Data dummy sudah ada. Lewati. (Reset: php artisan migrate:fresh --seed)');
            return;
        }

        $pw = Hash::make('password123');

        // ---------------------------------------------------------------
        // 1) EVENT + LOMBA + KRITERIA
        // ---------------------------------------------------------------
        $event = Event::create([
            'nama'                       => 'PERSIMANU Jepara 2026 (DUMMY)',
            'slug'                       => 'persimanu-jepara-2026-dummy',
            'deskripsi'                  => 'Event dummy untuk pengujian seluruh fitur.',
            'status'                     => 'aktif',
            'periode_pendaftaran_mulai'  => '2026-01-01',
            'periode_pendaftaran_selesai'=> '2026-12-31',
            'tanggal_pelaksanaan_mulai'  => '2026-08-15',
            'tanggal_pelaksanaan_selesai'=> '2026-08-17',
            'created_by'                 => null,
        ]);

        $lomba1 = Lomba::create(['event_id' => $event->id, 'nama' => 'Cerdas Cermat Kepramukaan', 'slug' => Str::slug('Cerdas Cermat Kepramukaan').'-d1', 'deskripsi' => 'Lomba dummy 1', 'status' => 'aktif', 'created_by' => null]);
        $lomba2 = Lomba::create(['event_id' => $event->id, 'nama' => 'Pionering & Simpul',       'slug' => Str::slug('Pionering & Simpul').'-d2',       'deskripsi' => 'Lomba dummy 2', 'status' => 'aktif', 'created_by' => null]);

        $komponen = ['Kekompakan & Yel-yel', 'Teknik & Keterampilan', 'Kerapian & Disiplin'];
        foreach ([$lomba1, $lomba2] as $lom) {
            foreach ($komponen as $i => $nama) {
                KriteriaKomponen::create(['lomba_id' => $lom->id, 'golongan' => 'penggalang', 'nama_komponen' => $nama, 'urutan' => $i + 1, 'is_active' => true]);
            }
        }
        $krit1 = $lomba1->kriterias()->orderBy('urutan')->pluck('id')->all();
        $krit2 = $lomba2->kriterias()->orderBy('urutan')->pluck('id')->all();

        // ---------------------------------------------------------------
        // 2) JURI (2) + penugasan ke lomba
        // ---------------------------------------------------------------
        $juri1 = $this->makeUser('Juri Satu',  'juri1@dummy.test', $pw, '081100000001');
        $juri2 = $this->makeUser('Juri Dua',   'juri2@dummy.test', $pw, '081100000002');
        $juri1->assignRole('juri');
        $juri2->assignRole('juri');
        $lomba1->juri()->sync([$juri1->id, $juri2->id]);
        $lomba2->juri()->sync([$juri1->id, $juri2->id]);

        // ---------------------------------------------------------------
        // 3) OPERATOR (5) + PANGKALAN (team)
        // ---------------------------------------------------------------
        $pangkalan = [
            ['op0@dummy.test', 'Operator MTs NU Jepara',   'MTs NU Jepara',   '20318001', 'MTs', 'Jepara'],
            ['op1@dummy.test', 'Operator MA NU Kudus',     'MA NU Kudus',     '20318002', 'MA',  'Kudus'],
            ['op2@dummy.test', 'Operator SMP NU Demak',    'SMP NU Demak',    '20318003', 'SMP', 'Demak'],
            ['op3@dummy.test', 'Operator MI NU Pati',      'MI NU Pati',      '20318004', 'MI',  'Pati'],
            ['op4@dummy.test', 'Operator MTs NU Rembang',  'MTs NU Rembang',  '20318005', 'MTs', 'Rembang'],
        ];
        $teams = []; $ops = [];
        foreach ($pangkalan as [$email, $nama, $teamName, $npsn, $jenjang, $kota]) {
            $u = $this->makeUser($nama, $email, $pw, '08120000'.substr($npsn, -4));
            $u->assignRole('operator-sekolah');
            $t = $u->ownedTeams()->create(['name' => $teamName, 'personal_team' => false]);
            $t->forceFill(['npsn' => $npsn, 'jenjang' => $jenjang, 'alamat' => "Jl. Pramuka, {$kota}", 'no_telp' => '08120000'.substr($npsn, -4)])->save();
            $u->forceFill(['current_team_id' => $t->id])->save();
            $teams[] = $t; $ops[] = $u;
        }
        [$t0, $t1, $t2, $t3, $t4] = $teams;

        // ---------------------------------------------------------------
        // 4) KONTINGEN (5) — campuran status biar semua flow ke-test
        // ---------------------------------------------------------------
        $kont0 = $this->makeKontingen($event->id, $t0->id, 'terverifikasi');
        $kont1 = $this->makeKontingen($event->id, $t1->id, 'terverifikasi');
        $kont2 = $this->makeKontingen($event->id, $t2->id, 'terverifikasi');
        $kont3 = $this->makeKontingen($event->id, $t3->id, 'menunggu_approval_pembayaran'); // butuh approve bayar
        $kont4 = $this->makeKontingen($event->id, $t4->id, 'menunggu_verifikasi_dokumen');  // butuh verifikasi dokumen

        // ---------------------------------------------------------------
        // 5) SISWA & PENDAMPING
        //    kontingen selesai → siswa+pendamping approved
        //    kont4 (verif dok) → siswa+pendamping pending (buat admin verifikasi)
        // ---------------------------------------------------------------
        $sis0 = $this->makeSiswa($kont0->id, 4, 'approved');
        $sis1 = $this->makeSiswa($kont1->id, 4, 'approved');
        $sis2 = $this->makeSiswa($kont2->id, 4, 'approved');
        $sis4 = $this->makeSiswa($kont4->id, 2, 'pending');

        $pend0 = $this->makePendamping($kont0->id, 'approved');
        $pend1 = $this->makePendamping($kont1->id, 'approved');
        $pend2 = $this->makePendamping($kont2->id, 'approved');
        $pend4 = $this->makePendamping($kont4->id, 'pending');

        // ---------------------------------------------------------------
        // 6) ALOKASI (lomba_kontingen) — hanya kontingen selesai, status SIAP
        //    nomor urut diatur biar ranking & juara umum terasa
        // ---------------------------------------------------------------
        $nomor = [
            $lomba1->id => [$kont0->id => 1, $kont1->id => 2, $kont2->id => 3],
            $lomba2->id => [$kont1->id => 1, $kont2->id => 2, $kont0->id => 3],
        ];
        $alokasiMap = []; // [lomba_id][kontingen_id] => ['id'=>..,'nomor'=>..]
        $setAlokasi = function ($lom, $kon, $pend, $sis) use ($nomor, &$alokasiMap) {
            $a = LombaKontingen::create([
                'lomba_id' => $lom->id, 'kontingen_id' => $kon->id, 'golongan' => 'penggalang',
                'pendamping_id' => $pend->id, 'nomor_urut_tampil' => $nomor[$lom->id][$kon->id], 'status' => 'siap',
            ]);
            $a->siswas()->sync($sis);
            $alokasiMap[$lom->id][$kon->id] = ['id' => $a->id, 'nomor' => $nomor[$lom->id][$kon->id]];
        };
        $setAlokasi($lomba1, $kont0, $pend0, $sis0);
        $setAlokasi($lomba1, $kont1, $pend1, $sis1);
        $setAlokasi($lomba1, $kont2, $pend2, $sis2);
        $setAlokasi($lomba2, $kont1, $pend1, $sis1);
        $setAlokasi($lomba2, $kont2, $pend2, $sis2);
        $setAlokasi($lomba2, $kont0, $pend0, $sis0);

        // ---------------------------------------------------------------
        // 7) PENILAIAN (2 juri per regu) — nilai diatur biar:
        //    lomba1: kont0(90) > kont1(85) > kont2(80)
        //    lomba2: kont1(92) > kont2(88) > kont0(78)
        //    => juara umum: kont1(5p) > kont0(4p) > kont2(3p)
        // ---------------------------------------------------------------
        $plan = [
            [$lomba1->id, $kont0->id, [88, 92], $krit1],
            [$lomba1->id, $kont1->id, [84, 86], $krit1],
            [$lomba1->id, $kont2->id, [79, 81], $krit1],
            [$lomba2->id, $kont1->id, [90, 94], $krit2],
            [$lomba2->id, $kont2->id, [87, 89], $krit2],
            [$lomba2->id, $kont0->id, [77, 79], $krit2],
        ];
        foreach ($plan as [$lomId, $konId, $vals, $kritIds]) {
            $meta = $alokasiMap[$lomId][$konId];
            foreach ([$juri1->id, $juri2->id] as $idx => $juriId) {
                $v = $vals[$idx];
                $p = Penilaian::create([
                    'lomba_id' => $lomId, 'kontingen_id' => $konId, 'juri_id' => $juriId,
                    'golongan' => 'penggalang', 'nomor_urut_tampil' => $meta['nomor'],
                    'nilai_akhir_juri' => $v, 'is_locked' => true, 'submitted_at' => now(),
                ]);
                foreach ($kritIds as $kid) {
                    PenilaianDetail::create(['penilaian_id' => $p->id, 'kriteria_komponen_id' => $kid, 'nilai' => $v]);
                }
            }
        }

        $this->command?->info('✅ Data dummy siap. Login pakai password: password123');
    }

    private function makeUser(string $name, string $email, string $pw, string $hp): User
    {
        $u = User::create(['name' => $name, 'email' => $email, 'password' => $pw, 'no_hp' => $hp, 'is_active' => true]);
        $u->forceFill(['email_verified_at' => now()])->save();
        return $u;
    }

    private function makeKontingen(int $eventId, int $teamId, string $status): Kontingen
    {
        return Kontingen::create([
            'event_id' => $eventId, 'team_id' => $teamId, 'status' => $status,
            'nama_kontingen' => 'Kontingen Dummy', 'contact_person' => 'CP Dummy', 'contact_phone' => '081200000000',
            'approved_by' => ($status !== 'menunggu_approval_pembayaran') ? 1 : null,
            'approved_at' => ($status !== 'menunggu_approval_pembayaran') ? now() : null,
            'finalized_at'=> ($status === 'terverifikasi') ? now() : null,
        ]);
    }

    private function makeSiswa(int $kontingenId, int $n, string $status): array
    {
        $ids = [];
        for ($i = 1; $i <= $n; $i++) {
            $s = \App\Models\Siswa::create([
                'kontingen_id' => $kontingenId, 'nama' => "Siswa Dummy {$i}", 'nisn' => '00'.str_pad($kontingenId.$i, 6, '0'),
                'jenis_kelamin' => $i % 2 ? 'L' : 'P', 'tempat_lahir' => 'Jepara', 'tanggal_lahir' => '2011-05-1'.($i % 9 + 1),
                'nama_orang_tua' => "Orang Tua {$i}", 'alamat' => 'Jl. Pramuka', 'no_telp' => '08130000000'.$i,
                'jenjang_pendidikan' => 'MTs', 'golongan_pramuka' => 'penggalang', 'golongan_darah' => 'O',
                'status_verifikasi' => $status,
            ]);
            $ids[] = $s->id;
        }
        return $ids;
    }

    private function makePendamping(int $kontingenId, string $status): \App\Models\Pendamping
    {
        return \App\Models\Pendamping::create([
            'kontingen_id' => $kontingenId, 'nama' => 'Pembina Dummy', 'jenis_kelamin' => 'L',
            'jabatan' => 'Pembina', 'pekerjaan' => 'Guru', 'golongan_binaan' => 'penggalang',
            'asal_instansi' => 'Pangkalan Dummy', 'tempat_lahir' => 'Jepara', 'tanggal_lahir' => '1985-03-12',
            'alamat' => 'Jl. Pramuka', 'no_telp' => '081400000000', 'kota' => 'Jepara', 'golongan_darah' => 'A',
            'status_verifikasi' => $status,
        ]);
    }
}