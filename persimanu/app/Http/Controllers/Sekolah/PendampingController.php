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
        abort_unless($pangkalan && $kontingen->team_id === $pangkalan->id, 403, 'Kontingen bukan milik pangkalan Anda.');
        abort_unless($kontingen->bolehIsiBiodata(), 403, 'Biodata hanya bisa diisi setelah pembayaran disetujui Admin.');
    }

    private function rules(): array
    {
        return [
            'nama'             => ['required', 'string', 'max:255'],
            'jenis_kelamin'    => ['required', Rule::in(['L', 'P'])],
            'jabatan'          => ['nullable', 'string', 'max:255'],
            'pekerjaan'        => ['nullable', 'string', 'max:255'],
            'asal_instansi'    => ['nullable', 'string', 'max:255'],
            'golongan_binaan'  => ['nullable', Rule::in(['penggalang_ramu', 'penggalang', 'penegak'])],
            'tempat_lahir'     => ['nullable', 'string', 'max:255'],
            'tanggal_lahir'    => ['nullable', 'date'],
            'alamat'           => ['nullable', 'string'],
            'no_telp'          => ['nullable', 'string', 'max:30'],
            'kota'             => ['nullable', 'string', 'max:255'],
            'golongan_darah'   => ['nullable', 'string', 'max:5'],
            // ✅ WAJIB: biar slot_index TIDAK ke-strip validate()
            'slot_index'       => ['required', 'integer', 'min:1'],
        ];
    }

    public function index(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);
        $kontingen->load('event');

        // ✅ kuota dari C.01
        $kuotaPutra = (int) ($kontingen->pendamping_putra ?? 0);
        $kuotaPutri = (int) ($kontingen->pendamping_putri ?? 0);

        $pendampings = $kontingen->pendampings()->orderBy('slot_index')->get();
        $pendampingPutra = $pendampings->where('jenis_kelamin', 'L')->values();
        $pendampingPutri = $pendampings->where('jenis_kelamin', 'P')->values();

        // ✅ generate slot putra
        $slotsPutra = [];
        for ($i = 0; $i < $kuotaPutra; $i++) {
            $existing = $pendampingPutra->firstWhere('slot_index', $i + 1);
            $slotsPutra[] = ['slot' => $i + 1, 'pendamping' => $existing, 'filled' => ! is_null($existing)];
        }
        // ✅ generate slot putri
        $slotsPutri = [];
        for ($i = 0; $i < $kuotaPutri; $i++) {
            $existing = $pendampingPutri->firstWhere('slot_index', $i + 1);
            $slotsPutri[] = ['slot' => $i + 1, 'pendamping' => $existing, 'filled' => ! is_null($existing)];
        }

        return inertia('Sekolah/Pendamping/Index', [
            'kontingen'  => $kontingen,
            'slotsPutra' => $slotsPutra,
            'slotsPutri' => $slotsPutri,
            'kuotaPutra' => $kuotaPutra,
            'kuotaPutri' => $kuotaPutri,
        ]);
    }

    public function create(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);

        $slot = (int) $request->query('slot', 1);
        $jk   = $request->query('jk', 'L');

        // ✅ guard: slot tidak boleh melebihi kuota
        $maxSlot = $jk === 'L' ? (int) $kontingen->pendamping_putra : (int) $kontingen->pendamping_putri;
        abort_unless($slot >= 1 && $slot <= max(1, $maxSlot), 403, 'Slot melebihi kuota yang ditentukan.');

        // ✅ kalau slot sudah terisi, lempar ke edit
        $existing = $kontingen->pendampings()->where('jenis_kelamin', $jk)->where('slot_index', $slot)->first();
        if ($existing) {
            return redirect()->route('sekolah.pendamping.edit', ['kontingen' => $kontingen, 'pendamping' => $existing]);
        }

        $kontingen->load('team'); // buat auto-fill asal_instansi
        return inertia('Sekolah/Pendamping/Form', [
            'kontingen'  => $kontingen,
            'pendamping' => null,
            'slot'       => $slot,
            'jk'         => $jk,
        ]);
    }

    public function store(Request $request, Kontingen $kontingen)
    {
        $this->authorizeKontingen($request, $kontingen);
        $data = $request->validate($this->rules());

        // ✅ guard anti slot dobel
        $dup = $kontingen->pendampings()
            ->where('jenis_kelamin', $data['jenis_kelamin'])
            ->where('slot_index', $data['slot_index'])
            ->first();
        if ($dup) {
            return redirect()->route('sekolah.pendamping.edit', ['kontingen' => $kontingen, 'pendamping' => $dup])
                ->with('error', 'Slot ini sudah terisi. Mengarahkan ke edit.');
        }

        $kontingen->pendampings()->create($data); // ✅ slot_index ikut tersimpan
        return redirect()->route('sekolah.pendamping.index', $kontingen)
            ->with('success', 'Data pendamping berhasil ditambahkan.')->setStatusCode(303);
    }

    public function edit(Request $request, Kontingen $kontingen, Pendamping $pendamping)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($pendamping->kontingen_id === $kontingen->id, 404);
        abort_unless($pendamping->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat diubah.');
        $kontingen->load('team');
        return inertia('Sekolah/Pendamping/Form', [
            'kontingen'  => $kontingen,
            'pendamping' => $pendamping,
            'slot'       => null,
            'jk'         => null,
        ]);
    }

    public function update(Request $request, Kontingen $kontingen, Pendamping $pendamping)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($pendamping->kontingen_id === $kontingen->id, 404);
        abort_unless($pendamping->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat diubah.');
        $data = $request->validate($this->rules());

        // ✅ guard anti slot dobel (kecuali diri sendiri)
        $dup = $kontingen->pendampings()
            ->where('jenis_kelamin', $data['jenis_kelamin'])
            ->where('slot_index', $data['slot_index'])
            ->where('id', '!=', $pendamping->id)
            ->first();
        if ($dup) {
            return back()->with('error', 'Slot ini sudah dipakai pendamping lain.')->setStatusCode(303);
        }

        $data['status_verifikasi'] = 'pending';
        $data['catatan_verifikasi'] = null;
        $pendamping->update($data);
        return redirect()->route('sekolah.pendamping.index', $kontingen)
            ->with('success', 'Data pendamping berhasil diupdate (menunggu verifikasi ulang).')->setStatusCode(303);
    }

    public function destroy(Request $request, Kontingen $kontingen, Pendamping $pendamping)
    {
        $this->authorizeKontingen($request, $kontingen);
        abort_unless($pendamping->kontingen_id === $kontingen->id, 404);
        abort_unless($pendamping->status_verifikasi !== 'approved', 403, 'Data yang sudah disetujui tidak dapat dihapus.');
        $pendamping->delete();
        return redirect()->route('sekolah.pendamping.index', $kontingen)
            ->with('success', 'Data pendamping berhasil dihapus.')->setStatusCode(303);
    }
}