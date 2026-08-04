<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Lomba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LombaController extends Controller
{
    private const GOLONGAN = ['penggalang_ramu', 'penggalang', 'penegak'];
    private const KATEGORI = ['PA', 'PI'];
    private const STATUS   = ['draft', 'aktif', 'selesai'];

    public function index(Request $request)
    {
        $lombas = Lomba::with(['event', 'kriteriaKomponens'])
            ->when($request->query('event_id'), fn ($q, $id) => $q->where('event_id', $id))
            ->latest()
            ->get();
        $events = Event::orderBy('nama')->get(['id', 'nama']);
        return inertia('Admin/Lombas/Index', compact('lombas', 'events'));
    }

    public function create()
    {
        return inertia('Admin/Lombas/Form', [
            'lomba'   => null,
            'events'  => Event::orderBy('nama')->get(['id', 'nama']),
            'prefill' => null,
        ]);
    }

    private function rules(): array
    {
        return [
            'event_id'                  => ['required', Rule::exists('events', 'id')],
            'nama'                      => ['required', 'string', 'max:255'],
            'deskripsi'                 => ['nullable', 'string'],
            'golongan'                  => ['required', Rule::in(self::GOLONGAN)],
            'kategori'                  => ['required', Rule::in(self::KATEGORI)],
            'status'                    => ['required', Rule::in(self::STATUS)],
            'komponens'                 => ['nullable', 'array', 'max:5'],
            'komponens.*.nama_komponen' => ['required', 'string', 'max:255'],
            'komponens.*.is_active'     => ['nullable', 'boolean'],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $lomba = Lomba::create([
            'event_id'   => $data['event_id'],
            'nama'       => $data['nama'],
            'slug'       => Str::slug($data['nama']) . '-' . Str::lower(Str::random(6)),
            'deskripsi'  => $data['deskripsi'] ?? null,
            'golongan'   => $data['golongan'],
            'kategori'   => $data['kategori'],
            'status'     => $data['status'],
            'created_by' => Auth::id(),
        ]);

        $this->syncKomponens($lomba, $data['komponens'] ?? [], $data['golongan']);

        return redirect()->route('admin.lombas.index')
            ->with('success', 'Lomba berhasil dibuat.')->setStatusCode(303);
    }

    public function edit(Lomba $lomba)
    {
        $lomba->load('kriteriaKomponens');
        return inertia('Admin/Lombas/Form', [
            'lomba'   => $lomba,
            'events'  => Event::orderBy('nama')->get(['id', 'nama']),
            'prefill' => null,
        ]);
    }

    public function update(Request $request, Lomba $lomba)
    {
        $data = $request->validate($this->rules());

        $lomba->update([
            'event_id'  => $data['event_id'],
            'nama'      => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'golongan'  => $data['golongan'],
            'kategori'  => $data['kategori'],
            'status'    => $data['status'],
        ]);

        $this->syncKomponens($lomba, $data['komponens'] ?? [], $data['golongan']);

        return redirect()->route('admin.lombas.index')
            ->with('success', 'Lomba berhasil diupdate.')->setStatusCode(303);
    }

    /** ✅ Poin 4: buka form create ter-prefill dari lomba yang di-duplikat */
    public function duplicate(Lomba $lomba)
    {
        $lomba->load('kriteriaKomponens');

        return inertia('Admin/Lombas/Form', [
            'lomba'  => null, // mode create → simpan sebagai entri BARU
            'events' => Event::orderBy('nama')->get(['id', 'nama']),
            'prefill' => [
                'event_id'  => $lomba->event_id,
                'nama'      => $lomba->nama,
                'deskripsi' => $lomba->deskripsi,
                'golongan'  => $lomba->golongan,
                'kategori'  => $lomba->kategori,
                'status'    => 'draft', // duplikat selalu mulai dari draft
                'komponens' => $lomba->kriteriaKomponens->map(fn ($k) => [
                    'nama_komponen' => $k->nama_komponen,
                    'is_active'     => $k->is_active,
                ])->values()->all(),
            ],
        ]);
    }

    public function destroy(Lomba $lomba)
    {
        // hapus komponen yang belum dipakai penilaian dulu (aman), baru lombanya
        $lomba->kriteriaKomponens()->whereDoesntHave('penilaianDetails')->delete();
        $lomba->delete();
        return redirect()->route('admin.lombas.index')
            ->with('success', 'Lomba berhasil dihapus.')->setStatusCode(303);
    }

    /**
     * Sync komponen penilaian: update yang ada, buat yang baru, hapus yang dihilangkan.
     * Komponen yang SUDAH dipakai di penilaian TIDAK dihapus (jaga integritas nilai juri).
     */
    private function syncKomponens(Lomba $lomba, array $komponens, string $golongan): void
    {
        $keepIds = [];
        foreach (array_values($komponens) as $i => $komp) {
            $payload = [
                'nama_komponen' => $komp['nama_komponen'],
                'golongan'      => $golongan, // ✅ komponen ikut golongan lomba
                'urutan'        => $i + 1,
                'is_active'     => $komp['is_active'] ?? true,
            ];
            // (form tidak mengirim id; selalu buat ulang urutan rapi)
            $new = $lomba->kriteriaKomponens()->create($payload);
            $keepIds[] = $new->id;
        }
        // hapus komponen lama yang tidak dipertahankan & belum dinilai
        $lomba->kriteriaKomponens()
            ->whereNotIn('id', $keepIds)
            ->whereDoesntHave('penilaianDetails')
            ->delete();
    }
}