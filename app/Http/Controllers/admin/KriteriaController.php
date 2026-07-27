<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KriteriaKomponen;
use App\Models\Lomba;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KriteriaController extends Controller
{
    public function index(Request $request)
    {
        $kriterias = KriteriaKomponen::with('lomba')
            ->when($request->query('lomba_id'), fn ($q, $id) => $q->where('lomba_id', $id))
            ->orderBy('lomba_id')
            ->orderBy('golongan')
            ->orderBy('urutan')
            ->get();

        $lombas = Lomba::orderBy('nama')->get(['id', 'nama']);

        return inertia('Admin/Kriterias/Index', compact('kriterias', 'lombas'));
    }

    public function create()
    {
        return inertia('Admin/Kriterias/Form', [
            'kriteria' => null,
            'lombas' => Lomba::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lomba_id'      => ['required', Rule::exists('lombas', 'id')],
            'golongan'      => ['required', Rule::in(['siaga', 'penggalang', 'penegak', 'pandega'])],
            'nama_komponen' => ['required', 'string', 'max:255'],
            'urutan'        => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['urutan']    = $data['urutan'] ?? 1;

        KriteriaKomponen::create($data);

        return redirect()
            ->route('admin.kriterias.index')
            ->with('success', 'Kriteria berhasil dibuat.')
            ->setStatusCode(303);
    }

    public function edit(KriteriaKomponen $kriteria)
    {
        return inertia('Admin/Kriterias/Form', [
            'kriteria' => $kriteria,
            'lombas' => Lomba::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function update(Request $request, KriteriaKomponen $kriteria)
    {
        $data = $request->validate([
            'lomba_id'      => ['required', Rule::exists('lombas', 'id')],
            'golongan'      => ['required', Rule::in(['siaga', 'penggalang', 'penegak', 'pandega'])],
            'nama_komponen' => ['required', 'string', 'max:255'],
            'urutan'        => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $kriteria->update($data);

        return redirect()
            ->route('admin.kriterias.index')
            ->with('success', 'Kriteria berhasil diupdate.')
            ->setStatusCode(303);
    }

    public function destroy(KriteriaKomponen $kriteria)
    {
        $kriteria->delete();

        return redirect()
            ->route('admin.kriterias.index')
            ->with('success', 'Kriteria berhasil dihapus.')
            ->setStatusCode(303);
    }
}