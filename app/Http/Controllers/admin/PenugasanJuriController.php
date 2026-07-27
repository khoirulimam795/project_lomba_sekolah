<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Lomba;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenugasanJuriController extends Controller
{
    public function index(Request $request)
    {
        $lombas = Lomba::with(['event', 'juri'])
            ->when($request->query('event_id'), fn ($q, $id) => $q->where('event_id', $id))
            ->orderBy('nama')
            ->get();

        $events = Event::orderBy('nama')->get(['id', 'nama']);

        return inertia('Admin/PenugasanJuri/Index', compact('lombas', 'events'));
    }

    public function edit(Lomba $lomba)
    {
        $lomba->load(['event', 'juri']);

        $juris = User::role('juri')->orderBy('name')->get(['id', 'name', 'email']);
        $assigned = $lomba->juri->pluck('id')->toArray();

        return inertia('Admin/PenugasanJuri/Edit', compact('lomba', 'juris', 'assigned'));
    }

    public function update(Request $request, Lomba $lomba)
    {
        $data = $request->validate([
            'juri_ids'   => ['nullable', 'array'],
            'juri_ids.*' => ['integer', Rule::exists('users', 'id')],
        ]);

        // hanya user ber-role juri yang boleh di-assign
        $validJuriIds = User::role('juri')
            ->whereIn('id', $data['juri_ids'] ?? [])
            ->pluck('id')
            ->toArray();

        $lomba->juri()->sync($validJuriIds);

        return redirect()
            ->route('admin.penugasan-juri.index')
            ->with('success', 'Penugasan juri berhasil disimpan.')
            ->setStatusCode(303);
    }
}