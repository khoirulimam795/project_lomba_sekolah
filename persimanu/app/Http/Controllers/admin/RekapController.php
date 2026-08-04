<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Juara;
use App\Models\Kontingen;
use App\Models\Lomba;
use App\Models\Penilaian;
use App\Models\PenilaianDetail;
use App\Exports\RekapLombaExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class RekapController extends Controller
{
    /** Golongan valid (3) — dipakai juara umum per-golongan. */
    private const GOLONGAN = ['penggalang_ramu', 'penggalang', 'penegak'];

    private function golLabel(string $gol): string
    {
        return [
            'penggalang_ramu' => 'Penggalang Ramu',
            'penggalang'      => 'Penggalang',
            'penegak'         => 'Penegak',
        ][$gol] ?? ucfirst($gol);
    }

    // ===================== 7A: REKAP PER LOMBA =====================

    public function index(Request $request)
    {
        $events = Event::orderByDesc('id')->get();
        $selectedEventId = $request->query('event_id') ? (int) $request->query('event_id') : null;
        $lombas = collect();
        if ($selectedEventId) {
            Event::findOrFail($selectedEventId);
            $lombas = Lomba::where('event_id', $selectedEventId)
                ->withCount('penilaians')
                ->orderBy('nama')
                ->get();
        }
        return inertia('Admin/Rekap/Index', compact('events', 'lombas', 'selectedEventId'));
    }

   public function show(Lomba $lomba)
{
    $lomba->load('event');

    $juriColumns = $this->juriColumns($lomba);          // ✅ kolom juri dihitung dulu
    $rekap       = $this->hitungRekap($lomba, $juriColumns); // ✅ ikut dikirim ke hitungRekap

    $finalized_at      = Juara::where('lomba_id', $lomba->id)->max('updated_at');
    $last_penilaian_at = Penilaian::where('lomba_id', $lomba->id)->max('submitted_at');

    $penilaianIds   = Penilaian::where('lomba_id', $lomba->id)->pluck('id');
    $last_revisi_at = Activity::where('subject_type', (new Penilaian)->getMorphClass())
        ->whereIn('subject_id', $penilaianIds)
        ->max('created_at');

    $last_change_at = collect([$last_penilaian_at, $last_revisi_at])
        ->filter()->map(fn ($d) => strtotime($d))->max();
    $last_change_at = $last_change_at ? date('Y-m-d H:i:s', $last_change_at) : null;

    // ✅ ARRAY EKSPLISIT: key 'juri_columns' (buat Vue) <- nilai $juriColumns (variabel PHP)
    return inertia('Admin/Rekap/Show', [
        'lomba'          => $lomba,
        'rekap'          => $rekap,
        'juri_columns'   => $juriColumns,
        'finalized_at'   => $finalized_at,
        'last_change_at' => $last_change_at,
    ]);
}

    /**
     * Urutan kolom juri yang konsisten lintas regu/golongan.
     * Ambil dari penugasan lomba; kalau kosong, fallback ke juri yang benar-benar menilai.
     */
    private function juriColumns(Lomba $lomba): array
    {
        $juris = $lomba->juri()->orderBy('name')->get();

        if ($juris->isEmpty()) {
            $juris = Penilaian::where('lomba_id', $lomba->id)
                ->with('juri')
                ->get()
                ->pluck('juri')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        }

        return $juris->map(fn ($j) => [
            'id'   => $j->id,
            'name' => $j->name ?? '-',
        ])->values()->all();
    }

    /**
     * Rekap per golongan. Tiap regu sekarang bawa `juri_scores` (array selaras
     * dengan $juriColumns) + `nilai_akhir` (rata-rata juri) + `jumlah_juri`.
     */
   private function hitungRekap(Lomba $lomba, array $juriColumns = []): array
{
    $juriIds    = array_column($juriColumns, 'id');
    $penilaians = Penilaian::where('lomba_id', $lomba->id)
        ->with(['kontingen.team', 'juri'])
        ->get();

    $rekap = [];
    foreach ($penilaians->groupBy('golongan') as $gol => $group) {
        $rows = $group->groupBy('kontingen_id')
            ->map(function ($g) use ($juriIds) {
                $byJuri = $g->keyBy('juri_id');

                if ($juriIds) {
                    // mode tampilan: skor per juri, selaras urutan kolom (null = juri ini tak menilai regu ini)
                    $scores = [];
                    foreach ($juriIds as $jid) {
                        $scores[] = isset($byJuri[$jid]) ? round((float) $byJuri[$jid]->nilai_akhir_juri, 2) : null;
                    }
                    $vals = array_filter($scores, fn ($v) => $v !== null);
                } else {
                    // mode tanpa kolom (finalize / export): pakai semua penilaian
                    $scores = [];
                    $vals   = $g->map(fn ($p) => round((float) $p->nilai_akhir_juri, 2))->all();
                }

                // jaring pengaman: kalau kebetulan kosong, fallback ke rata-rata group
                if (empty($vals)) {
                    $vals   = $g->map(fn ($p) => round((float) $p->nilai_akhir_juri, 2))->all();
                    $scores = $vals;
                }

                return [
                    'kontingen_id' => $g->first()->kontingen_id,
                    'team_name'    => $g->first()->kontingen->team->name ?? '-',
                    'juri_scores'  => $scores,
                    'nilai_akhir'  => round(array_sum($vals) / count($vals), 2),
                    'jumlah_juri'  => count($vals),
                ];
            })
            ->values()->sortByDesc('nilai_akhir')->values()
            ->map(fn ($r, $i) => $r + ['rank' => $i + 1])->values()->all();

        $rekap[$gol] = $rows;
    }

    return $rekap;
}

    /**
     * ✅ FIX: simpan SEMUA juara 1-2-3 per golongan dulu, baru return.
     * (Versi lama return di dalam loop → cuma 1 medali kesimpen.)
     */
   public function finalize(Lomba $lomba)
{
    $rekap  = $this->hitungRekap($lomba);   // 1 argumen aman (default [])
    $medali = [1 => 'emas', 2 => 'perak', 3 => 'perunggu'];

    DB::transaction(function () use ($lomba, $rekap, $medali) {
        Juara::where('lomba_id', $lomba->id)->delete();
        foreach ($rekap as $gol => $rows) {
            foreach ($rows as $r) {
                if ($r['rank'] > 3) continue;
                Juara::create([
                    'event_id'     => $lomba->event_id,
                    'lomba_id'     => $lomba->id,
                    'kontingen_id' => $r['kontingen_id'],
                    'golongan'     => $gol,
                    'juara'        => $r['rank'],
                    'medali'       => $medali[$r['rank']],
                    'nilai_akhir'  => $r['nilai_akhir'],
                    'is_final'     => true,
                ]);
            }
        }
    });

    event(new \App\Events\JuaraUpdated($lomba->event_id, "Juara \"{$lomba->nama}\" diperbarui"));

    // ✅ return SETELAH semua juara tersimpan (bukan di dalam loop)
    return back()
        ->with('success', 'Juara lomba berhasil dihitung & disimpan.')
        ->setStatusCode(303);
}

    /** Susun data export (Excel & CSV) = struktur tabel rekap (per-juri + rata-rata). */
    private function buildExport(Lomba $lomba): array
    {
        $juriColumns = $this->juriColumns($lomba);
        $rekap       = $this->hitungRekap($lomba, $juriColumns);

        $headings = array_merge(
            ['Peringkat', 'Pangkalan / Kontingen', 'Golongan'],
            array_map(fn ($jc) => 'Nilai ' . $jc['name'], $juriColumns),
            ['Nilai Akhir (Rata-rata)', 'Jumlah Juri']
        );

        $rows = [];
        foreach ($rekap as $gol => $items) {
            $label = $this->golLabel($gol);
            foreach ($items as $r) {
                $row = [$r['rank'], $r['team_name'], $label];
                foreach ($juriColumns as $i => $jc) {
                    $row[] = $r['juri_scores'][$i] ?? '';
                }
                $row[] = $r['nilai_akhir'];
                $row[] = $r['jumlah_juri'];
                $rows[] = $row;
            }
        }

        return [
            'title'    => 'REKAP NILAI — ' . $lomba->nama,
            'sub'      => $lomba->event->nama ?? '',
            'headings' => $headings,
            'rows'     => $rows,
        ];
    }

    public function exportExcel(Lomba $lomba)
    {
        $lomba->load('event');
        $e = $this->buildExport($lomba);
        // baris judul + sub + header + data → satu sheet rapi
        $data = array_merge([[$e['title']], [$e['sub']], [$e['headings']]], $e['rows']);

        return Excel::download(
            new RekapLombaExport($data),
            'rekap-' . Str::slug($lomba->nama) . '.xlsx'
        );
    }

    public function exportCsv(Lomba $lomba): StreamedResponse
    {
        $lomba->load('event');
        $e        = $this->buildExport($lomba);
        $filename = 'rekap-' . Str::slug($lomba->nama) . '.csv';

        return response()->streamDownload(function () use ($e) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, $e['headings'], ';');
            foreach ($e['rows'] as $r) {
                fputcsv($out, $r, ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ===================== 7B-1: JUARA UMUM PER GOLONGAN =====================

    public function juaraUmum(Request $request)
    {
        $events = Event::orderByDesc('id')->get();
        $selectedEventId = $request->query('event_id') ? (int) $request->query('event_id') : null;

        // ✅ DIPISAH per golongan: tiap golongan punya ranking + totalMedal + lombaDinilai sendiri
        $perGolongan = [];
        foreach (self::GOLONGAN as $gol) {
            $perGolongan[$gol] = [
                'label'        => $this->golLabel($gol),
                'ranking'      => [],
                'totalMedal'   => ['emas' => 0, 'perak' => 0, 'perunggu' => 0],
                'lombaDinilai' => 0,
            ];
        }

        if ($selectedEventId) {
            $event = Event::findOrFail($selectedEventId);
            $juaras = Juara::where('event_id', $event->id)
                ->where('is_final', true)
                ->with(['kontingen.team', 'lomba'])
                ->get();

            foreach (self::GOLONGAN as $gol) {
                $gJuaras = $juaras->where('golongan', $gol);

                $rows = $gJuaras->groupBy('kontingen_id')->map(function ($g) {
                    return [
                        'kontingen_id' => $g->first()->kontingen_id,
                        'team_name'    => $g->first()->kontingen->team->name ?? '-',
                        'jenjang'      => $g->first()->kontingen->team->jenjang ?? null,
                        'emas'         => $g->where('medali', 'emas')->count(),
                        'perak'        => $g->where('medali', 'perak')->count(),
                        'perunggu'     => $g->where('medali', 'perunggu')->count(),
                        'details'      => $g->map(fn ($j) => [
                            'lomba_nama' => $j->lomba->nama ?? '-',
                            'golongan'   => $j->golongan,
                            'medali'     => $j->medali,
                            'nilai'      => $j->nilai_akhir,
                        ])->values()->all(),
                    ];
                });

                $total = ['emas' => 0, 'perak' => 0, 'perunggu' => 0];
                foreach ($rows as $r) {
                    $total['emas']     += $r['emas'];
                    $total['perak']    += $r['perak'];
                    $total['perunggu'] += $r['perunggu'];
                }

                $arr = $rows->values()->all();
                // ranking olimpiade: emas↓ → perak↓ → perunggu↓
                usort($arr, fn ($a, $b) =>
                    [$b['emas'], $b['perak'], $b['perunggu'], $a['kontingen_id']]
                    <=> [$a['emas'], $a['perak'], $a['perunggu'], $b['kontingen_id']]);

                $perGolongan[$gol]['ranking'] = collect($arr)
                    ->map(fn ($r, $i) => $r + ['rank' => $i + 1])->values()->all();
                $perGolongan[$gol]['totalMedal']   = $total;
                $perGolongan[$gol]['lombaDinilai'] = (int) $gJuaras->pluck('lomba_id')->unique()->count();
            }
        }

        return inertia('Admin/Rekap/JuaraUmum', compact('events', 'selectedEventId', 'perGolongan'));
    }

    // ===================== 7B-2 + 7B-3: EDIT NILAI + AUDIT =====================

    public function editNilai(Lomba $lomba, Kontingen $kontingen, string $golongan)
    {
        $lomba->load('event');
        abort_unless($kontingen->event_id === $lomba->event_id, 404);

        $kriterias = $lomba->kriterias()
            ->where('is_active', true)->where('golongan', $golongan)
            ->orderBy('urutan')->get(['id', 'nama_komponen', 'urutan']);

        $penilaians = Penilaian::where('lomba_id', $lomba->id)
            ->where('kontingen_id', $kontingen->id)
            ->where('golongan', $golongan)
            ->with(['juri', 'details'])
            ->orderBy('nomor_urut_tampil')
            ->get()
            ->map(fn ($p) => [
                'id'               => $p->id,
                'juri_name'        => $p->juri->name ?? '-',
                'juri_email'       => $p->juri->email ?? null,
                'nilai_akhir_juri' => $p->nilai_akhir_juri,
                'submitted_at'     => optional($p->submitted_at)->format('d M Y H:i'),
                'is_locked'        => $p->is_locked,
                'nilai'            => $p->details->pluck('nilai', 'kriteria_komponen_id'),
            ]);

        $ids = $penilaians->pluck('id');
        $audit = Activity::where('subject_type', (new Penilaian)->getMorphClass())
            ->whereIn('subject_id', $ids)
            ->with('causer')
            ->latest()->limit(50)->get()
            ->map(fn ($a) => [
                'who'   => $a->causer->name ?? 'Sistem',
                'when'  => $a->created_at->format('d M Y H:i'),
                'desc'  => $a->description,
                'props' => $a->properties ? $a->properties->toArray() : [],
            ]);

        return inertia('Admin/Rekap/EditNilai', compact('lomba', 'kontingen', 'golongan', 'kriterias', 'penilaians', 'audit'));
    }

    public function updateNilai(Request $request, Lomba $lomba, Penilaian $penilaian)
    {
        abort_unless($penilaian->lomba_id === $lomba->id, 404);

        $komponenAktif = $lomba->kriterias()
            ->where('is_active', true)->where('golongan', $penilaian->golongan)
            ->pluck('id')->sort()->values()->all();

        $data = $request->validate([
            'nilai'   => ['required', 'array'],
            'nilai.*' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $submittedIds = collect(array_keys($data['nilai']))->map(fn ($x) => (int) $x)->sort()->values()->all();
        abort_unless($submittedIds === $komponenAktif, 422, 'Komponen penilaian tidak lengkap atau tidak cocok.');

        $oldMap = $penilaian->details->pluck('nilai', 'kriteria_komponen_id')->toArray();
        $oldAvg = (float) $penilaian->nilai_akhir_juri;

        DB::transaction(function () use ($penilaian, $data) {
            foreach ($data['nilai'] as $kid => $val) {
                PenilaianDetail::updateOrCreate(
                    ['penilaian_id' => $penilaian->id, 'kriteria_komponen_id' => (int) $kid],
                    ['nilai' => (int) $val]
                );
            }
            $newAvg = round(collect($data['nilai'])->avg(), 2);
            $penilaian->update(['nilai_akhir_juri' => $newAvg, 'is_locked' => true]);
        });

        $newAvg = round(collect($data['nilai'])->avg(), 2);

        activity()
            ->performedOn($penilaian)
            ->causedBy(Auth::user())
            ->withProperties([
                'old'     => $oldMap,
                'new'     => $data['nilai'],
                'old_avg' => $oldAvg,
                'new_avg' => $newAvg,
                'juri_id' => $penilaian->juri_id,
            ])
            ->log('revisi-nilai');

        return back()
            ->with('success', "Nilai juri direvisi: {$oldAvg} → {$newAvg}. Tercatat di audit log.")
            ->setStatusCode(303);
    }
}