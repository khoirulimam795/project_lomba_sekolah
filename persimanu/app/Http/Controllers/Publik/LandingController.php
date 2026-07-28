<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Juara;
use App\Models\Lomba;
use App\Models\Penilaian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    private const VALID_GOLONGAN = ['penggalang_ramu', 'penggalang', 'penegak'];

    public function index(Request $request)
    {
        $event = Event::where('status', 'aktif')->latest()->first()
            ?? Event::latest()->first();

        return $this->render($event, $request);
    }

    public function show(Request $request, Event $event)
    {
        return $this->render($event, $request);
    }

    private function render(?Event $event, Request $request)
    {
        // ✅ Ambil parameter filter golongan dari query string
        $selectedGolongan = $request->query('golongan');
        if ($selectedGolongan && !in_array($selectedGolongan, self::VALID_GOLONGAN)) {
            $selectedGolongan = null; // abaikan nilai invalid
        }

        if (!$event) {
            return inertia('Publik/Landing', [
                'event' => null, 'stats' => null, 'phases' => [],
                'standings' => [], 'leaderboards' => [], 'liveStatus' => null,
                'selectedGolongan' => null,
            ]);
        }

        return inertia('Publik/Landing', [
            'event'            => $event,
            'liveStatus'       => $this->liveStatus($event),
            'stats'            => $this->stats($event),
            'phases'           => $this->phases($event),
            'standings'        => $this->standings($event, $selectedGolongan),
            'leaderboards'     => $this->leaderboards($event),
            'selectedGolongan' => $selectedGolongan,
        ]);
    }

    private function liveStatus(Event $event): string
    {
        $now = Carbon::now();
        if ($now->lt(Carbon::parse($event->tanggal_pelaksanaan_mulai)->startOfDay())) return 'upcoming';
        if ($now->gt(Carbon::parse($event->tanggal_pelaksanaan_selesai)->endOfDay())) return 'ended';
        return 'live';
    }

    private function stats(Event $event): array
    {
        $lombaIds = $event->lombas()->pluck('id');
        return [
            'pangkalan' => (int) \App\Models\Kontingen::where('event_id', $event->id)
                ->distinct('team_id')->count('team_id'),
            'regu'      => (int) \App\Models\LombaKontingen::whereIn('lomba_id', $lombaIds)->count(),
            'lomba'     => (int) $lombaIds->count(),
            'juri'      => (int) \DB::table('lomba_juri')->whereIn('lomba_id', $lombaIds)
                ->distinct('juri_id')->count('juri_id'),
        ];
    }

    private function phases(Event $event): array
    {
        $now = Carbon::now();
        $mk = fn($m, $s) => [
            'mulai' => $m, 'selesai' => $s,
            'active' => $now->between(Carbon::parse($m)->startOfDay(), Carbon::parse($s)->endOfDay()),
        ];
        return [
            ['key' => 'daftar', 'label' => 'Pendaftaran', 'icon' => '📝']
                + $mk($event->periode_pendaftaran_mulai, $event->periode_pendaftaran_selesai),
            ['key' => 'lomba', 'label' => 'Pelaksanaan Lomba', 'icon' => '🏕️']
                + $mk($event->tanggal_pelaksanaan_mulai, $event->tanggal_pelaksanaan_selesai),
            ['key' => 'juara', 'label' => 'Pengumuman Juara', 'icon' => '🏆']
                + ['mulai' => $event->tanggal_pelaksanaan_selesai, 'selesai' => $event->tanggal_pelaksanaan_selesai,
                    'active' => $now->gt(Carbon::parse($event->tanggal_pelaksanaan_selesai)->endOfDay())],
        ];
    }

    /**
     * ✅ Papan medali dengan FILTER GOLONGAN + Ranking Olimpiade (tanpa poin)
     */
    private function standings(Event $event, ?string $selectedGolongan): array
    {
        $query = Juara::where('event_id', $event->id)
            ->where('is_final', true)
            ->with('kontingen.team');

        // ✅ Filter golongan jika dipilih
        if ($selectedGolongan) {
            $query->where('golongan', $selectedGolongan);
        }

        $juaras = $query->get();

        if ($juaras->isEmpty()) return [];

        // ✅ Ranking Olimpiade: emas↓ → perak↓ → perunggu↓ (TANPA bobot/poin)
        $rows = $juaras->groupBy('kontingen_id')->map(function ($g) {
            $emas     = $g->where('medali', 'emas')->count();
            $perak    = $g->where('medali', 'perak')->count();
            $perunggu = $g->where('medali', 'perunggu')->count();
            return [
                'kontingen_id' => $g->first()->kontingen_id,
                'team_name'    => $g->first()->kontingen->team->name ?? '-',
                'jenjang'      => $g->first()->kontingen->team->jenjang ?? null,
                'emas'         => $emas,
                'perak'        => $perak,
                'perunggu'     => $perunggu,
            ];
        })->values()->all();

        usort($rows, fn($a, $b) =>
            [$b['emas'], $b['perak'], $b['perunggu'], $a['kontingen_id']]
            <=> [$a['emas'], $a['perak'], $a['perunggu'], $b['kontingen_id']]
        );

        return collect($rows)->map(fn($r, $i) => $r + ['rank' => $i + 1])->values()->all();
    }

  private function leaderboards(Event $event): array
{
    return $event->lombas()->orderBy('nama')->get()->map(function (Lomba $lomba) {
        // ✅ Ambil SEMUA alokasi siap (bukan cuma yang sudah dinilai)
        $alokasis = \App\Models\LombaKontingen::where('lomba_id', $lomba->id)
            ->where('status', 'siap')
            ->with('kontingen.team')
            ->get()
            ->keyBy('kontingen_id');

        // Ambil penilaian yang sudah ada
        $penilaianMap = Penilaian::where('lomba_id', $lomba->id)
            ->with(['kontingen.team', 'juri'])
            ->get()
            ->groupBy('kontingen_id');

        // ✅ Gabung: semua alokasi siap + nilai (kalau ada)
        $rows = $alokasis->map(function ($alokasi) use ($penilaianMap) {
            $kontingenId = $alokasi->kontingen_id;
            $penilaians = $penilaianMap[$kontingenId] ?? collect();

            return [
                'kontingen_id' => $kontingenId,
                'team_name'    => $alokasi->kontingen->team->name ?? '-',
                'golongan'     => $alokasi->golongan ?? null,
                'kategori'     => $alokasi->kategori ?? null,
                'nilai_akhir'  => $penilaians->isNotEmpty()
                    ? round((float) $penilaians->avg('nilai_akhir_juri'), 2)
                    : null,
                'jumlah_juri'  => $penilaians->count(),
                'juri_scores'  => $penilaians->map(fn ($p) => [
                    'nama'  => $p->juri->name ?? '-',
                    'nilai' => $p->nilai_akhir_juri,
                ])->values()->all(),
            ];
        })
        ->values()
        ->sortByDesc(fn ($r) => $r['nilai_akhir'] ?? -1)
        ->values()
        ->map(fn ($r, $i) => $r + ['rank' => $i + 1])
        ->values()
        ->all();

        return ['lomba' => ['id' => $lomba->id, 'nama' => $lomba->nama], 'rows' => $rows];
    })->values()->all();
}
}