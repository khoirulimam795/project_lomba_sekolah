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
    public function index(Request $request)
    {
        $lombas = Lomba::with('event')
            ->when($request->query('event_id'), fn ($q, $id) => $q->where('event_id', $id))
            ->latest()
            ->get();

        $events = Event::orderBy('nama')->get(['id', 'nama']);

        return inertia('Admin/Lombas/Index', compact('lombas', 'events'));
    }

    public function create()
    {
        return inertia('Admin/Lombas/Form', [
            'lomba' => null,
            'events' => Event::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id'  => ['required', Rule::exists('events', 'id')],
            'nama'      => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status'    => ['required', Rule::in(['draft', 'aktif', 'selesai'])],
        ]);

        $data['slug'] = Str::slug($data['nama']) . '-' . Str::lower(Str::random(6));
        $data['created_by'] = Auth::id();

        Lomba::create($data);

        return redirect()
            ->route('admin.lombas.index')
            ->with('success', 'Lomba berhasil dibuat.')
            ->setStatusCode(303);
    }

    public function edit(Lomba $lomba)
    {
        return inertia('Admin/Lombas/Form', [
            'lomba' => $lomba,
            'events' => Event::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function update(Request $request, Lomba $lomba)
    {
        $data = $request->validate([
            'event_id'  => ['required', Rule::exists('events', 'id')],
            'nama'      => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status'    => ['required', Rule::in(['draft', 'aktif', 'selesai'])],
        ]);

        $lomba->update($data);

        return redirect()
            ->route('admin.lombas.index')
            ->with('success', 'Lomba berhasil diupdate.')
            ->setStatusCode(303);
    }

    public function destroy(Lomba $lomba)
    {
        $lomba->delete();

        return redirect()
            ->route('admin.lombas.index')
            ->with('success', 'Lomba berhasil dihapus.')
            ->setStatusCode(303);
    }
}