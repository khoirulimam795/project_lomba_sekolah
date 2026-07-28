<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KriteriaKomponen;
use App\Models\Lomba;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KriteriaController extends Controller
{
    private const GOLONGAN_OPTIONS = ['penggalang_ramu', 'penggalang', 'penegak'];

    public function index()
    {
        $lombas = Lomba::with(['kriteriaKomponens' => fn ($q) => $q->orderBy('urutan')])
            ->orderBy('nama')
            ->get();

        return inertia('Admin/Kriterias/Index', [
            'lombas' => $lombas,
        ]);
    }

    public function create(Request $request)
    {
        $lombaId = $request->query('lomba_id');
        abort_unless($lombaId && Lomba::find($lombaId), 404, 'Lomba tidak ditemukan.');

        $lomba = Lomba::findOrFail($lombaId);

        // Ambil komponen yang sudah ada untuk lomba ini (untuk edit mode)
        $existingKomponens = KriteriaKomponen::where('lomba_id', $lombaId)
            ->orderBy('urutan')
            ->get()
            ->map(fn ($k) => [
                'id'              => $k->id,
                'nama_komponen'   => $k->nama_komponen,
                'golongan'        => $k->golongan,
                'urutan'          => $k->urutan,
                'is_active'       => $k->is_active,
            ])
            ->toArray();

        return inertia('Admin/Kriterias/Form', [
            'lomba'             => $lomba,
            'golonganOptions'   => self::GOLONGAN_OPTIONS,
            'existingKomponens' => $existingKomponens,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lomba_id'                    => ['required', Rule::exists('lombas', 'id')],
            'komponens'                   => ['required', 'array', 'min:1', 'max:5'],
            'komponens.*.nama_komponen'   => ['required', 'string', 'max:255'],
            'komponens.*.golongan'        => ['required', Rule::in(self::GOLONGAN_OPTIONS)],
            'komponens.*.urutan'          => ['required', 'integer', 'min:1'],
            'komponens.*.is_active'       => ['nullable', 'boolean'],
        ]);

        // Hapus komponen lama untuk lomba ini, lalu buat ulang
        KriteriaKomponen::where('lomba_id', $data['lomba_id'])->delete();

        foreach ($data['komponens'] as $komp) {
            KriteriaKomponen::create([
                'lomba_id'        => $data['lomba_id'],
                'nama_komponen'   => $komp['nama_komponen'],
                'golongan'        => $komp['golongan'],
                'urutan'          => $komp['urutan'],
                'is_active'       => $komp['is_active'] ?? true,
            ]);
        }

        return redirect()
            ->route('admin.kriterias.index')
            ->with('success', 'Komponen kriteria berhasil disimpan.')
            ->setStatusCode(303);
    }

    public function edit(Lomba $lomba)
    {
        $existingKomponens = KriteriaKomponen::where('lomba_id', $lomba->id)
            ->orderBy('urutan')
            ->get()
            ->map(fn ($k) => [
                'id'              => $k->id,
                'nama_komponen'   => $k->nama_komponen,
                'golongan'        => $k->golongan,
                'urutan'          => $k->urutan,
                'is_active'       => $k->is_active,
            ])
            ->toArray();

        return inertia('Admin/Kriterias/Form', [
            'lomba'             => $lomba,
            'golonganOptions'   => self::GOLONGAN_OPTIONS,
            'existingKomponens' => $existingKomponens,
        ]);
    }

    public function update(Request $request, Lomba $lomba)
    {
        $data = $request->validate([
            'komponens'                   => ['required', 'array', 'min:1', 'max:5'],
            'komponens.*.id'              => ['nullable', 'integer'],
            'komponens.*.nama_komponen'   => ['required', 'string', 'max:255'],
            'komponens.*.golongan'        => ['required', Rule::in(self::GOLONGAN_OPTIONS)],
            'komponens.*.urutan'          => ['required', 'integer', 'min:1'],
            'komponens.*.is_active'       => ['nullable', 'boolean'],
        ]);

        // Sync: update existing, create new, delete removed
        $existingIds = [];
        foreach ($data['komponens'] as $komp) {
            if (!empty($komp['id'])) {
                KriteriaKomponen::where('id', $komp['id'])
                    ->where('lomba_id', $lomba->id)
                    ->update([
                        'nama_komponen' => $komp['nama_komponen'],
                        'golongan'      => $komp['golongan'],
                        'urutan'        => $komp['urutan'],
                        'is_active'     => $komp['is_active'] ?? true,
                    ]);
                $existingIds[] = $komp['id'];
            } else {
                $newKomp = KriteriaKomponen::create([
                    'lomba_id'        => $lomba->id,
                    'nama_komponen'   => $komp['nama_komponen'],
                    'golongan'        => $komp['golongan'],
                    'urutan'          => $komp['urutan'],
                    'is_active'       => $komp['is_active'] ?? true,
                ]);
                $existingIds[] = $newKomp->id;
            }
        }

        // Hapus yang tidak lagi ada di array
        KriteriaKomponen::where('lomba_id', $lomba->id)
            ->whereNotIn('id', $existingIds)
            ->delete();

        return redirect()
            ->route('admin.kriterias.index')
            ->with('success', 'Komponen kriteria berhasil diupdate.')
            ->setStatusCode(303);
    }

    public function destroy(KriteriaKomponen $kriteriaKomponen)
    {
        $kriteriaKomponen->delete();

        return back()
            ->with('success', 'Komponen kriteria berhasil dihapus.')
            ->setStatusCode(303);
    }
}