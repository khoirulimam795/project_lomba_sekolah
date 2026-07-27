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
            'golongan_pramuka'   => ['nullable', Rule::in(['siaga', 'penggalang', 'penegak', 'pandega'])],
            'golongan_darah'     => ['nullable', 'string', 'max:5'],
            'surat_kesehatan'    => ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf'],
        ];
    }

    public function index(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);
        $kontingen->load('event');

        // AMAN: hitung dokumen per siswa lewat relasi media (morph).
        // Kalau model Siswa belum implements HasMedia, blok withCount ini yang bikin 500 —
        // kalau lo kena 500 di halaman ini, berarti HasMedia-nya belum nempel (lihat catatan bawah).
        $siswas = $kontingen->siswas()
            ->withCount(['media as doc_count' => fn ($q) => $q->where('collection_name', 'surat_kesehatan')])
            ->orderBy('nama')
            ->get();

        // 👇 DEBUG: buka console browser, harusnya muncul array isi siswa (bukan [])
        // \Illuminate\Support\Facades\Log::info('INDEX siswas count = ' . $siswas->count());

        return inertia('Sekolah/Siswa/Index', [
            'kontingen' => $kontingen,   // key: kontingen
            'siswas'    => $siswas,      // key: siswas  ← WAJIB persis ini
        ]);
    }

    public function create(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);

        return inertia('Sekolah/Siswa/Form', [
            'kontingen'   => $kontingen,
            'siswa'       => null,        // key: siswa  ← WAJIB persis ini
            'existingDoc' => null,        // key: existingDoc
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

        return redirect()
            ->route('sekolah.siswa.index', $kontingen)
            ->with('success', 'Data siswa berhasil ditambahkan.')
            ->setStatusCode(303);
    }

    public function edit(Request $request, Kontingen $kontingen, Siswa $siswa)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($siswa->kontingen_id === $kontingen->id, 404);
        abort_unless($siswa->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat diubah.');

        $doc = $siswa->getFirstMedia('surat_kesehatan');

        return inertia('Sekolah/Siswa/Form', [
            'kontingen' => $kontingen,
            'siswa'     => $siswa,        // key: siswa  ← INI yang bikin form edit ke-isi
            'existingDoc' => $doc ? [
                'url'  => $doc->getUrl(),
                'mime' => $doc->mime_type,
                'name' => $doc->file_name,
            ] : null,
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

        return redirect()
            ->route('sekolah.siswa.index', $kontingen)
            ->with('success', 'Data siswa berhasil diupdate (menunggu verifikasi ulang).')
            ->setStatusCode(303);
    }

    public function destroy(Request $request, Kontingen $kontingen, Siswa $siswa)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($siswa->kontingen_id === $kontingen->id, 404);
        abort_unless($siswa->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat dihapus.');
        $siswa->delete();
        return redirect()
            ->route('sekolah.siswa.index', $kontingen)
            ->with('success', 'Data siswa berhasil dihapus.')
            ->setStatusCode(303);
    }
}