<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\LombaKontingen;
use Illuminate\Http\Request;

class NomorUrutController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::orderByDesc('id')->get();

        $selectedEventId = $request->query('event_id') ? (int) $request->query('event_id') : null;

        $rows = collect();

        if ($selectedEventId) {
            Event::findOrFail($selectedEventId); // 404 kalau event nggak ada

            $rows = LombaKontingen::with(['lomba', 'kontingen.team', 'pendamping'])
                ->withCount('siswas')
                ->whereHas('lomba', fn ($q) => $q->where('event_id', $selectedEventId))
                ->orderBy('lomba_id')
                ->orderBy('golongan')
                ->get()
                ->map(fn ($a) => [
                    'id'                => $a->id,
                    'lomba_id'          => $a->lomba_id,
                    'lomba_nama'        => $a->lomba->nama ?? '-',
                    'lomba_status'      => $a->lomba->status ?? 'draft',
                    'golongan'          => $a->golongan,
                    'team_name'         => $a->kontingen->team->name ?? '-',
                    'pendamping_name'   => $a->pendamping->nama ?? null,
                    'siswa_count'       => $a->siswas_count,
                    'nomor_urut_tampil' => $a->nomor_urut_tampil,
                    'status'            => $a->status,
                ]);
        }

        return inertia('Admin/NomorUrut/Index', compact('events', 'selectedEventId', 'rows'));
    }

    /**
     * Simpan nomor urut massal (TANPA mengubah status / kunci).
     * Cek duplikat nomor per (lomba + golongan) di dalam payload.
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'rows'                       => ['required', 'array'],
            'rows.*.id'                  => ['required', 'integer', 'exists:lomba_kontingen,id'],
            'rows.*.nomor_urut_tampil'   => ['nullable', 'integer', 'min:1'],
        ]);

        $meta = LombaKontingen::with('lomba')
            ->whereIn('id', collect($data['rows'])->pluck('id'))
            ->get()
            ->keyBy('id');

        $seen = [];
        foreach ($data['rows'] as $r) {
            $n = $r['nomor_urut_tampil'];
            if ($n === null) continue;

            $m = $meta[$r['id']] ?? null;
            if (! $m) continue;

            $key = $m->lomba_id . '|' . $m->golongan . '|' . $n;
            if (isset($seen[$key])) {
                return back()->withErrors([
                    'rows' => "Nomor urut {$n} duplikat pada lomba \"{$m->lomba->nama}\" golongan {$m->golongan}.",
                ])->setStatusCode(303);
            }
            $seen[$key] = true;
        }

        foreach ($data['rows'] as $r) {
            LombaKontingen::where('id', $r['id'])->update([
                'nomor_urut_tampil' => $r['nomor_urut_tampil'],
            ]);
        }

        return back()
            ->with('success', 'Nomor urut tampil berhasil disimpan.')
            ->setStatusCode(303);
    }

    /**
     * Kunci regu: set nomor urut + status = siap.
     * Nomor wajib terisi & unik terhadap regu terkunci lain di lomba+golongan yang sama.
     */
    public function lock(Request $request, LombaKontingen $alokasi)
    {
        $data = $request->validate([
            'nomor_urut_tampil' => ['required', 'integer', 'min:1'],
        ]);

        if (! in_array($alokasi->status, ['draft', 'siap'])) {
            abort(403, 'Alokasi sudah selesai dan tidak dapat diubah.');
        }

        $n = $data['nomor_urut_tampil'];

        $conflict = LombaKontingen::where('lomba_id', $alokasi->lomba_id)
            ->where('golongan', $alokasi->golongan)
            ->where('nomor_urut_tampil', $n)
            ->where('status', 'siap')
            ->where('id', '!=', $alokasi->id)
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'lock' => "Nomor urut {$n} sudah dipakai regu terkunci lain pada golongan {$alokasi->golongan}.",
            ])->setStatusCode(303);
        }

        $alokasi->update(['nomor_urut_tampil' => $n, 'status' => 'siap']);

        return back()
            ->with('success', "Regu dikunci dengan nomor urut {$n}.")
            ->setStatusCode(303);
    }

    /**
     * Buka kunci regu (balik ke draft) — biar admin bisa ralat.
     */
    public function unlock(LombaKontingen $alokasi)
    {
        if (! in_array($alokasi->status, ['draft', 'siap'])) {
            abort(403, 'Alokasi sudah selesai dan tidak dapat diubah.');
        }

        $alokasi->update(['status' => 'draft']);

        return back()
            ->with('success', 'Kunci regu dibuka — operator dapat mengubah alokasi lagi.')
            ->setStatusCode(303);
    }
}