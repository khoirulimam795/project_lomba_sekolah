<?php

namespace App\Http\Controllers\Sekolah;

use App\Http\Controllers\Controller;
use App\Models\Kontingen;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    private function authorizeKontingen(Request $request, Kontingen $kontingen): void
    {
        $pangkalan = $request->user()->ownedTeams()->first();
        abort_unless($pangkalan && $kontingen->team_id === $pangkalan->id, 403, 'Kontingen bukan milik pangkalan Anda.');
        abort_unless($kontingen->bolehIsiBiodata(), 403, 'Biodata hanya bisa diisi setelah pembayaran disetujui Admin.');
    }

    private function rules(): array
    {
        return [
            'nama'               => ['required', 'string', 'max:255'],
            'nisn'               => ['nullable', 'string', 'max:50'],
            'jenis_kelamin'      => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir'       => ['nullable', 'string', 'max:255'],
            'tanggal_lahir'      => ['nullable', 'date'],
            'nama_orang_tua'     => ['nullable', 'string', 'max:255'],
            'alamat'             => ['nullable', 'string'],
            'no_telp'            => ['nullable', 'string', 'max:30'],
            'jenjang_pendidikan' => ['nullable', Rule::in(['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK'])],
            // ✅ ENUM BARU: hanya 3 golongan (siaga & pandega dihapus)
            'golongan_pramuka'   => ['nullable', Rule::in(['penggalang_ramu', 'penggalang', 'penegak'])],
            'golongan_darah'     => ['nullable', 'string', 'max:5'],
            'surat_kesehatan'    => ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf'],
        ];
    }

    public function index(Request $request, Kontingen $kontingen)
{
    $this->authorizeKontingen($request, $kontingen);
    $kontingen->load('event');

    // ✅ Ambil kuota dari kontingen
    $kuotaPutra = (int) ($kontingen->peserta_putra ?? 0);
    $kuotaPutri = (int) ($kontingen->peserta_putri ?? 0);

    // ✅ Siswa yang sudah diisi
    $siswas = $kontingen->siswas()
        ->withCount(['media as doc_count' => fn ($q) => $q->where('collection_name', 'surat_kesehatan')])
        ->orderBy('slot_index')
        ->get();

    // ✅ Group by jenis_kelamin
    $siswaPutra = $siswas->where('jenis_kelamin', 'L')->values();
    $siswaPutri = $siswas->where('jenis_kelamin', 'P')->values();

    // ✅ Generate slot kosong
    $slotsPutra = [];
    for ($i = 0; $i < $kuotaPutra; $i++) {
        $existing = $siswaPutra->firstWhere('slot_index', $i + 1);
        $slotsPutra[] = [
            'slot' => $i + 1,
            'siswa' => $existing,
            'filled' => !is_null($existing),
        ];
    }

    $slotsPutri = [];
    for ($i = 0; $i < $kuotaPutri; $i++) {
        $existing = $siswaPutri->firstWhere('slot_index', $i + 1);
        $slotsPutri[] = [
            'slot' => $i + 1,
            'siswa' => $existing,
            'filled' => !is_null($existing),
        ];
    }

    return inertia('Sekolah/Siswa/Index', [
        'kontingen'   => $kontingen,
        'slotsPutra'  => $slotsPutra,
        'slotsPutri'  => $slotsPutri,
        'kuotaPutra'  => $kuotaPutra,
        'kuotaPutri'  => $kuotaPutri,
    ]);
}

public function create(Request $request, Kontingen $kontingen)
{
    $this->authorizeKontingen($request, $kontingen);

    $slot = (int) $request->query('slot', 1);
    $jk = $request->query('jk', 'L');

    // ✅ Validasi: slot tidak boleh melebihi kuota
    $maxSlot = $jk === 'L' ? (int) $kontingen->peserta_putra : (int) $kontingen->peserta_putri;
    abort_unless($slot >= 1 && $slot <= $maxSlot, 403, 'Slot melebihi kuota yang ditentukan.');

    // ✅ Cek apakah slot sudah terisi
    $existing = $kontingen->siswas()
        ->where('jenis_kelamin', $jk)
        ->where('slot_index', $slot)
        ->first();

    if ($existing) {
        return redirect()->route('sekolah.siswa.edit', [
            'kontingen' => $kontingen,
            'siswa' => $existing,
        ]);
    }

    return inertia('Sekolah/Siswa/Form', [
        'kontingen'   => $kontingen,
        'siswa'       => null,
        'existingDoc' => null,
        'slot'        => $slot,
        'jk'          => $jk,
    ]);
}

    public function store(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);
        $data = $request->validate($this->rules());
        $file = $request->file('surat_kesehatan');
        unset($data['surat_kesehatan']);

        $siswa = $kontingen->siswas()->create($data);
        if ($file) {
            $siswa->addMediaFromRequest('surat_kesehatan')->toMediaCollection('surat_kesehatan');
        }

        return redirect()->route('sekolah.siswa.index', $kontingen)
            ->with('success', 'Data siswa berhasil ditambahkan.')->setStatusCode(303);
    }

    public function edit(Request $request, Kontingen $kontingen, Siswa $siswa)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($siswa->kontingen_id === $kontingen->id, 404);
        abort_unless($siswa->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat diubah.');

        $doc = $siswa->getFirstMedia('surat_kesehatan');
        return inertia('Sekolah/Siswa/Form', [
            'kontingen' => $kontingen,
            'siswa'     => $siswa,
            'existingDoc' => $doc ? ['url' => $doc->getUrl(), 'mime' => $doc->mime_type, 'name' => $doc->file_name] : null,
        ]);
    }

    public function update(Request $request, Kontingen $kontingen, Siswa $siswa)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($siswa->kontingen_id === $kontingen->id, 404);
        abort_unless($siswa->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat diubah.');

        $data = $request->validate($this->rules());
        $file = $request->file('surat_kesehatan');
        unset($data['surat_kesehatan']);

        $data['status_verifikasi'] = 'pending';
        $data['catatan_verifikasi'] = null;
        $siswa->update($data);
        if ($file) {
            $siswa->addMediaFromRequest('surat_kesehatan')->toMediaCollection('surat_kesehatan');
        }

        return redirect()->route('sekolah.siswa.index', $kontingen)
            ->with('success', 'Data siswa berhasil diupdate (menunggu verifikasi ulang).')->setStatusCode(303);
    }

    public function destroy(Request $request, Kontingen $kontingen, Siswa $siswa)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($siswa->kontingen_id === $kontingen->id, 404);
        abort_unless($siswa->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat dihapus.');
        $siswa->delete();

        return redirect()->route('sekolah.siswa.index', $kontingen)
            ->with('success', 'Data siswa berhasil dihapus.')->setStatusCode(303);
    }
}