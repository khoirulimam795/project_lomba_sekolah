<?php

namespace App\Http\Controllers\Sekolah;

use App\Http\Controllers\Controller;
use App\Models\Kontingen;
use App\Models\Pendamping;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PendampingController extends Controller
{
    private function authorizeKontingen(Request $request, Kontingen $kontingen): void
    {
        $pangkalan = $request->user()->ownedTeams()->first();

        abort_unless(
            $pangkalan && $kontingen->team_id === $pangkalan->id,
            403,
            'Kontingen bukan milik pangkalan Anda.'
        );

        abort_unless(
            $kontingen->bolehIsiBiodata(),
            403,
            'Biodata hanya bisa diisi setelah pembayaran disetujui Admin.'
        );
    }

    private function rules(): array
    {
        return [
            'nama'             => ['required', 'string', 'max:255'],
            'jenis_kelamin'    => ['required', Rule::in(['L', 'P'])],
            'jabatan'          => ['nullable', 'string', 'max:255'],
            'pekerjaan'        => ['nullable', 'string', 'max:255'],
            'asal_instansi'    => ['nullable', 'string', 'max:255'],
            'golongan_binaan'  => ['nullable', Rule::in(['siaga', 'penggalang', 'penegak', 'pandega'])],
            'tempat_lahir'     => ['nullable', 'string', 'max:255'],
            'tanggal_lahir'    => ['nullable', 'date'],
            'alamat'           => ['nullable', 'string'],
            'no_telp'          => ['nullable', 'string', 'max:30'],
            'kota'             => ['nullable', 'string', 'max:255'],
            'golongan_darah'   => ['nullable', 'string', 'max:5'],
        ];
    }

    public function index(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);

        $kontingen->load('event');
        $pendampings = $kontingen->pendampings()->orderBy('nama')->get();

        return inertia('Sekolah/Pendamping/Index', compact('kontingen', 'pendampings'));
    }

    public function create(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);

        return inertia('Sekolah/Pendamping/Form', [
            'kontingen' => $kontingen,
            'pendamping' => null,
        ]);
    }

    public function store(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);

        $kontingen->pendampings()->create($request->validate($this->rules()));

        return redirect()
            ->route('sekolah.pendamping.index', $kontingen)
            ->with('success', 'Data pendamping berhasil ditambahkan.')
            ->setStatusCode(303);
    }

    public function edit(Request $request, Kontingen $kontingen, Pendamping $pendamping)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($pendamping->kontingen_id === $kontingen->id, 404);
        abort_unless($pendamping->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat diubah.');

        return inertia('Sekolah/Pendamping/Form', [
            'kontingen' => $kontingen,
            'pendamping' => $pendamping,
        ]);
    }

    public function update(Request $request, Kontingen $kontingen, Pendamping $pendamping)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($pendamping->kontingen_id === $kontingen->id, 404);
        abort_unless($pendamping->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat diubah.');

        $data = $request->validate($this->rules());
        $data['status_verifikasi'] = 'pending';
        $data['catatan_verifikasi'] = null;

        $pendamping->update($data);

        return redirect()
            ->route('sekolah.pendamping.index', $kontingen)
            ->with('success', 'Data pendamping berhasil diupdate (menunggu verifikasi ulang).')
            ->setStatusCode(303);
    }

    public function destroy(Request $request, Kontingen $kontingen, Pendamping $pendamping)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($pendamping->kontingen_id === $kontingen->id, 404);
        abort_unless($pendamping->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat dihapus.');

        $pendamping->delete();

        return redirect()
            ->route('sekolah.pendamping.index', $kontingen)
            ->with('success', 'Data pendamping berhasil dihapus.')
            ->setStatusCode(303);
    }
}