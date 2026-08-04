<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Team;
use App\Models\Event;
use App\Models\Lomba;
use App\Models\KriteriaKomponen;
use App\Models\Kontingen;
use App\Models\Siswa;
use App\Models\Pendamping;
use App\Models\LombaKontingen;
use App\Models\Penilaian;
use App\Models\PenilaianDetail;
use App\Models\Juara;

class DummySeeder extends Seeder
{
    private const GOLONGAN = ['penggalang_ramu', 'penggalang', 'penegak'];
    private const JK = ['L', 'P'];

    public function run(): void
    {
        // ================= ROLE =================
        foreach (['admin', 'juri', 'operator-sekolah'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // ================= USERS =================
        $admin = $this->makeUser('Admin PERSIMANU', 'admin@persimanu.test', 'admin', 'Panitia Pusat');
        $juri1 = $this->makeUser('Juri Satu', 'juri1@persimanu.test', 'juri', 'Dewan Juri 1');
        $juri2 = $this->makeUser('Juri Dua', 'juri2@persimanu.test', 'juri', 'Dewan Juri 2');
        $juri3 = $this->makeUser('Juri Tiga', 'juri3@persimanu.test', 'juri', 'Dewan Juri 3');
        $juris = [$juri1, $juri2, $juri3];

        /// ================= EVENT =================
$event = Event::firstOrCreate(
    ['nama' => 'PERSIMANU JEPARA 2026'],
    [
        'slug' => 'persimanu-jepara-2026',
        'status' => 'aktif',
        'periode_pendaftaran_mulai' => now()->subDays(20)->toDateString(),
        'periode_pendaftaran_selesai' => now()->addDays(10)->toDateString(),
        'tanggal_pelaksanaan_mulai' => now()->subDays(2)->toDateString(),
        'tanggal_pelaksanaan_selesai' => now()->addDays(2)->toDateString(),
        'created_by' => 1, // ✅ FIX: admin user ID = 1
    ]
);

        // ================= LOMBA + KOMPONEN =================
        $komponenDefault = ['Kerapian', 'Kekompakan', 'Kreativitas', 'Ketepatan Waktu'];
        $daftarLomba = [
            ['Pionering', 'penggalang_ramu', 'PA'],
            ['Pionering', 'penggalang_ramu', 'PI'],
            ['Pionering', 'penggalang', 'PA'],
            ['Pionering', 'penggalang', 'PI'],
            ['Pionering', 'penegak', 'PA'],
            ['Pionering', 'penegak', 'PI'],
            ['Cerdas Cermat', 'penggalang', 'PA'],
            ['Cerdas Cermat', 'penggalang', 'PI'],
            ['Cerdas Cermat', 'penegak', 'PA'],
            ['Cerdas Cermat', 'penegak', 'PI'],
            ['Semboyan & Isyarat', 'penggalang_ramu', 'PA'],
            ['Semboyan & Isyarat', 'penggalang_ramu', 'PI'],
        ];

        $lombas = [];
        foreach ($daftarLomba as $dl) {
            [$nama, $gol, $kat] = $dl;
            $lomba = $this->makeLomba($event, $nama, $gol, $kat);
            // komponen untuk golongan lomba ini
            foreach ($komponenDefault as $i => $kn) {
                KriteriaKomponen::firstOrCreate(
                    ['lomba_id' => $lomba->id, 'golongan' => $gol, 'nama_komponen' => $kn],
                    ['urutan' => $i + 1, 'is_active' => true]
                );
            }
            $lombas[] = $lomba;
        }

        // ================= PENUGASAN JURI (semua juri → semua lomba) =================
        foreach ($lombas as $lomba) {
            foreach ($juris as $juri) {
                $exists = DB::table('lomba_juri')
                    ->where('lomba_id', $lomba->id)->where('juri_id', $juri->id)->exists();
                if (!$exists) {
                    DB::table('lomba_juri')->insert([
                        'lomba_id' => $lomba->id,
                        'juri_id' => $juri->id,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }

        // ================= KONTINGEN + SISWA + PENDAMPING =================
        $namaPangkalan = [
            ['MTs Negeri 1 Jepara', 'MTs'],
            ['MTs Mathaliul Falah', 'MTs'],
            ['MA Nahdlatul Ulama', 'MA'],
            ['MI Miftahul Huda', 'MI'],
        ];

        $kontingens = [];
        foreach ($namaPangkalan as $idx => $np) {
            [$namaTeam, $jenjang] = $np;
            $team = $this->makeTeam($namaTeam, $jenjang, '0812' . str_pad($idx + 1, 7, '0', STR_PAD_LEFT));
            $kontingen = $this->makeKontingen($event, $team, $namaTeam);
            $kontingens[] = ['kontingen' => $kontingen, 'team' => $team];
        }

        // siswa per kontingen: 3 putra + 3 putri, golongan campuran
        foreach ($kontingens as $c) {
            $kontingen = $c['kontingen'];
            $slot = ['L' => 1, 'P' => 1];
            foreach (self::JK as $jk) {
                for ($i = 0; $i < 3; $i++) {
                    $gol = self::GOLONGAN[array_rand(self::GOLONGAN)];
                    Siswa::firstOrCreate(
                        ['kontingen_id' => $kontingen->id, 'jenis_kelamin' => $jk, 'slot_index' => $slot[$jk]],
                        [
                            'nama' => 'Siswa ' . ($jk === 'L' ? 'Putra' : 'Putri') . ' ' . $slot[$jk] . ' - ' . $kontingen->nama_kontingen,
                            'nisn' => '00' . rand(1000000, 9999999),
                            'golongan_pramuka' => $gol,
                            'tempat_lahir' => 'Jepara',
                            'tanggal_lahir' => now()->subYears(rand(12, 17))->toDateString(),
                            'jenjang_pendidikan' => 'MTs',
                            'golongan_darah' => ['A', 'B', 'O', 'AB'][rand(0, 3)],
                            'alamat' => 'Jl. Pemuda No. ' . rand(1, 99) . ' Jepara',
                            'no_telp' => '0857' . rand(10000000, 99999999),
                            'status_verifikasi' => 'approved',
                            'catatan_verifikasi' => null,
                        ]
                    );
                    $slot[$jk]++;
                }
            }
            // 1 pendamping
            Pendamping::firstOrCreate(
                ['kontingen_id' => $kontingen->id, 'slot_index' => 1],
                [
                    'nama' => 'Pembina ' . $kontingen->nama_kontingen,
                    'jenis_kelamin' => 'L',
                    'jabatan' => 'PEMBINA PENDAMPING',
                    'pekerjaan' => 'Guru',
                    'asal_instansi' => $kontingen->nama_kontingen,
                    'golongan_binaan' => 'penggalang',
                    'no_telp' => '0813' . rand(10000000, 99999999),
                    'status_verifikasi' => 'approved',
                    'catatan_verifikasi' => null,
                ]
            );
        }

        // ================= ALOKASI + PENILAIAN + JUARA =================
        $medali = [1 => 'emas', 2 => 'perak', 3 => 'perunggu'];

        foreach ($lombas as $lomba) {
            $gol = $this->col($lomba, 'golongan');
            $kat = $this->col($lomba, 'kategori');
            $jkTarget = $kat === 'PI' ? 'P' : 'L';

            // kumpulkan regu: tiap kontingen yang punya ≥3 siswa match golongan+kategori
            $regus = [];
            foreach ($kontingens as $c) {
                $kontingen = $c['kontingen'];
                $siswaMatch = Siswa::where('kontingen_id', $kontingen->id)
                    ->where('jenis_kelamin', $jkTarget)
                    ->where('golongan_pramuka', $gol)
                    ->where('status_verifikasi', 'approved')
                    ->limit(3)->get();
                if ($siswaMatch->count() < 3) continue;

                $pendamping = Pendamping::where('kontingen_id', $kontingen->id)
                    ->where('status_verifikasi', 'approved')->first();

                $alokasi = LombaKontingen::firstOrCreate(
                    ['lomba_id' => $lomba->id, 'kontingen_id' => $kontingen->id],
                    array_merge(
                        [
                            'golongan' => $gol,
                            'pendamping_id' => $pendamping?->id,
                            'status' => 'siap',
                            'nomor_urut_tampil' => count($regus) + 1,
                        ],
                        $this->ifHasColumn('lomba_kontingen', 'kategori', ['kategori' => $kat])
                    )
                );
                $alokasi->siswas()->sync($siswaMatch->pluck('id'));
                $regus[] = ['alokasi' => $alokasi, 'kontingen' => $kontingen];
            }

            // penilaian: tiap juri nilai tiap regu
            foreach ($regus as $ri => $regu) {
                $komponens = KriteriaKomponen::where('lomba_id', $lomba->id)
                    ->where('golongan', $gol)->where('is_active', true)->orderBy('urutan')->get();

                foreach ($juris as $juri) {
                    $sudah = Penilaian::where('lomba_id', $lomba->id)
                        ->where('kontingen_id', $regu['kontingen']->id)
                        ->where('juri_id', $juri->id)
                        ->where('golongan', $gol)->exists();
                    if ($sudah) continue;

                    $nilaiKomponen = [];
                    foreach ($komponens as $k) {
                        $nilaiKomponen[$k->id] = rand(70, 95);
                    }
                    $rata = round(collect($nilaiKomponen)->avg(), 2);

                    $penilaian = Penilaian::create([
                        'lomba_id' => $lomba->id,
                        'kontingen_id' => $regu['kontingen']->id,
                        'juri_id' => $juri->id,
                        'golongan' => $gol,
                        'nomor_urut_tampil' => $regu['alokasi']->nomor_urut_tampil,
                        'nilai_akhir_juri' => $rata,
                        'is_locked' => true,
                        'submitted_at' => now()->subMinutes(rand(10, 300)),
                    ]);
                    foreach ($nilaiKomponen as $kid => $val) {
                        PenilaianDetail::create([
                            'penilaian_id' => $penilaian->id,
                            'kriteria_komponen_id' => $kid,
                            'nilai' => $val,
                        ]);
                    }
                }
            }

            // juara 1-2-3 per golongan lomba ini (dari rata-rata antar regu)
            $ranking = collect($regus)->map(function ($regu) use ($lomba, $gol) {
                $avg = Penilaian::where('lomba_id', $lomba->id)
                    ->where('kontingen_id', $regu['kontingen']->id)
                    ->where('golongan', $gol)->avg('nilai_akhir_juri');
                return ['kontingen_id' => $regu['kontingen']->id, 'avg' => round((float) $avg, 2)];
            })->sortByDesc('avg')->values();

            foreach ($ranking->take(3) as $rank => $row) {
                Juara::firstOrCreate(
                    ['lomba_id' => $lomba->id, 'kontingen_id' => $row['kontingen_id'], 'golongan' => $gol],
                    [
                        'event_id' => $event->id,
                        'juara' => $rank + 1,
                        'medali' => $medali[$rank + 1],
                        'nilai_akhir' => $row['avg'],
                        'is_final' => true,
                    ]
                );
            }
        }

        $this->command?->info('✅ Dummy data selesai: ' . count($lombas) . ' lomba, ' . count($kontingens) . ' kontingen, penilaian + juara terbekukan.');
    }

    // ================= HELPERS =================

    private function makeUser(string $name, string $email, string $role, string $teamName): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password'), 'is_active' => true, 'email_verified_at' => now()]
        );
        $team = $user->ownedTeams()->firstOrCreate(
            ['name' => $teamName],
            ['personal_team' => true, 'jenjang' => 'SMP']
        );
        $user->forceFill(['current_team_id' => $team->id])->save();
        if (!$user->hasRole($role)) $user->assignRole($role);
        return $user;
    }

   private function makeTeam(string $name, string $jenjang, string $telp): Team
{
    return Team::firstOrCreate(
        ['name' => $name],
        [
            'user_id'       => 1, // ✅ FIX: admin user ID = 1 (dibuat pertama di makeUser)
            'jenjang'       => $jenjang,
            'npsn'          => '2' . rand(1000000, 9999999),
            'no_telp'       => $telp,
            'alamat'        => 'Jepara',
            'personal_team' => false,
        ]
    );
}

    private function makeLomba(Event $event, string $nama, string $gol, string $kat): Lomba
    {
        $attrs = [
            'event_id' => $event->id,
            'nama' => $nama . ' ' . $this->golShort($gol) . ' ' . ($kat === 'PA' ? 'Putra' : 'Putri'),
            'slug' => \Illuminate\Support\Str::slug($nama . '-' . $gol . '-' . $kat) . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5)),
            'deskripsi' => 'Lomba ' . $nama . ' golongan ' . $this->golShort($gol) . ' kategori ' . ($kat === 'PA' ? 'Putra' : 'Putri') . '.',
            'status' => 'aktif',
            'created_by' => 1,
        ];
        $attrs = array_merge($attrs, $this->ifHasColumn('lombas', 'golongan', ['golongan' => $gol]));
        $attrs = array_merge($attrs, $this->ifHasColumn('lombas', 'kategori', ['kategori' => $kat]));
        return Lomba::firstOrCreate(['event_id' => $event->id, 'slug' => $attrs['slug']], $attrs);
    }

    private function makeKontingen(Event $event, Team $team, string $nama): Kontingen
    {
        return Kontingen::firstOrCreate(
            ['event_id' => $event->id, 'team_id' => $team->id],
            array_merge(
                [
                    'status' => 'terverifikasi',
                    'nama_kontingen' => $nama,
                    'nama_kepala_madrasah' => 'Kepala ' . $nama,
                    'asal_instansi' => $nama,
                    'contact_person' => 'Operator ' . $nama,
                    'contact_phone' => $team->no_telp ?? '081200000000',
                    'pendamping_putra' => 1,
                    'pendamping_putri' => 0,
                    'peserta_putra' => 3,
                    'peserta_putri' => 3,
                ],
                $this->ifHasColumn('kontingens', 'catatan_pembayaran', ['catatan_pembayaran' => null])
            )
        );
    }

    private function golShort(string $gol): string
    {
        return ['penggalang_ramu' => 'Penggalang Ramu', 'penggalang' => 'Penggalang', 'penegak' => 'Penegak'][$gol] ?? $gol;
    }

    /** Ambil nilai kolom model kalau kolomnya ada di tabel (aman kalau belum migrate). */
    private function col($model, string $column)
    {
        return Schema::hasColumn($model->getTable(), $column) ? $model->{$column} : null;
    }

    /** Return $value cuma kalau kolom ada di tabel. */
    private function ifHasColumn(string $table, string $column, array $value): array
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column) ? $value : [];
    }
}