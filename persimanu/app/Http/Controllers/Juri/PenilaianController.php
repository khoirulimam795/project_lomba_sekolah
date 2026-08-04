<?php

namespace App\Http\Controllers\Juri;

use App\Http\Controllers\Controller;
use App\Models\Lomba;
use App\Models\LombaKontingen;
use App\Models\Penilaian;
use App\Models\PenilaianDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PenilaianController extends Controller
{
    /** Pastikan lomba benar-benar ditugaskan ke juri yang login (IDOR guard). */
    private function lombaMilikJuri(Lomba $lomba): void
    {
        abort_unless(
            $lomba->juri()->where('users.id', Auth::id())->exists(),
            404,
            'Lomba ini tidak ditugaskan kepada Anda.'
        );
    }

    public function index()
{
    $juri = Auth::user();

    $lombas = Lomba::whereHas('juri', fn ($q) => $q->where('users.id', $juri->id))
        ->with(['event', 'kriteriaKomponens' => fn($q) => $q->where('is_active', true)->orderBy('urutan')])
        ->withCount(['alokasi as regu_siap' => fn ($q) => $q->where('status', 'siap')])
        ->orderBy('nama')
        ->get()
        ->map(function ($lomba) use ($juri) {
            // ✅ Cek apakah juri ini sudah submit penilaian untuk lomba ini
            $lomba->sudah_dinilai = Penilaian::where('lomba_id', $lomba->id)
                ->where('juri_id', $juri->id)
                ->exists();
            
            return $lomba;
        });

    return inertia('Juri/Penilaian/Index', compact('lombas'));
}

    public function show(Request $request, Lomba $lomba)
    {
        $this->lombaMilikJuri($lomba);
        $lomba->load('event');

        // Golongan yang punya kriteria aktif di lomba ini
        $golonganAktif = $lomba->kriterias()
            ->where('is_active', true)
            ->distinct()
            ->pluck('golongan')
            ->sort()
            ->values();

        // Kriteria per golongan (ringkas: id, nama, urutan)
        $kriteriaByGolongan = $lomba->kriterias()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get()
            ->groupBy('golongan')
            ->map(fn ($g) => $g->values()->map(fn ($k) => [
                'id'            => $k->id,
                'nama_komponen' => $k->nama_komponen,
                'urutan'        => $k->urutan,
            ]));

        // Golongan terpilih (validasi)
        $selected = $request->query('golongan');
        if (! $selected || ! $golonganAktif->contains($selected)) {
            $selected = null;
        }

        // Regu SIAP di golongan terpilih + status penilaian juri ini per regu
        $regus = collect();
        if ($selected) {
            $alokasi = LombaKontingen::with('kontingen.team')
                ->withCount('siswas')
                ->where('lomba_id', $lomba->id)
                ->where('golongan', $selected)
                ->where('status', 'siap')
                ->orderBy('nomor_urut_tampil')
                ->get();

            $penilaianMap = Penilaian::with('details')
                ->where('lomba_id', $lomba->id)
                ->where('juri_id', Auth::id())
                ->where('golongan', $selected)
                ->get()
                ->keyBy('kontingen_id');

            $regus = $alokasi->map(fn ($a) => [
                'id'            => $a->id,
                'kontingen_id'  => $a->kontingen_id,
                'team_name'     => $a->kontingen->team->name ?? '-',
                'siswa_count'   => $a->siswas_count,
                'nomor_urut'    => $a->nomor_urut_tampil,
                'penilaian'     => $penilaianMap[$a->kontingen_id] ?? null,
            ]);
        }

        return inertia('Juri/Penilaian/Show', compact(
            'lomba', 'golonganAktif', 'kriteriaByGolongan', 'selected', 'regus'
        ));
    }

    public function store(Request $request, Lomba $lomba, LombaKontingen $alokasi)
    {
        $this->lombaMilikJuri($lomba);

        abort_unless($alokasi->lomba_id === $lomba->id, 404);
        abort_unless($alokasi->status === 'siap', 422, 'Regu belum dikunci untuk dinilai.');

        $golonganAktif = $lomba->kriterias()->where('is_active', true)
            ->distinct()->pluck('golongan')->all();

        $data = $request->validate([
            'golongan' => ['required', Rule::in($golonganAktif)],
            'nilai'    => ['required', 'array'],
            'nilai.*'  => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        abort_unless($alokasi->golongan === $data['golongan'], 422, 'Golongan tidak cocok dengan regu.');

        // Komponen aktif harus persis sama dengan yang dikirim (id & jumlah)
        $komponenAktif = $lomba->kriterias()
            ->where('is_active', true)
            ->where('golongan', $data['golongan'])
            ->pluck('id')->sort()->values()->all();

        $submittedIds = collect(array_keys($data['nilai']))
            ->map(fn ($x) => (int) $x)->sort()->values()->all();

        abort_unless($submittedIds === $komponenAktif, 422, 'Komponen penilaian tidak lengkap atau tidak cocok.');

        // Cegah menilai regu yang sama dua kali
        $sudah = Penilaian::where('lomba_id', $lomba->id)
            ->where('kontingen_id', $alokasi->kontingen_id)
            ->where('juri_id', Auth::id())
            ->where('golongan', $data['golongan'])
            ->exists();

        abort_if($sudah, 422, 'Anda sudah menilai regu ini.');

        $rata = round(collect($data['nilai'])->avg(), 2);

        DB::transaction(function () use ($lomba, $alokasi, $data, $rata) {
            $penilaian = Penilaian::create([
                'lomba_id'          => $lomba->id,
                'kontingen_id'      => $alokasi->kontingen_id,
                'juri_id'           => Auth::id(),
                'golongan'          => $data['golongan'],
                'nomor_urut_tampil' => $alokasi->nomor_urut_tampil,
                'nilai_akhir_juri'  => $rata,
                'is_locked'         => true,
                'submitted_at'      => now(),
            ]);

            foreach ($data['nilai'] as $kid => $val) {
                PenilaianDetail::create([
                    'penilaian_id'       => $penilaian->id,
                    'kriteria_komponen_id' => (int) $kid,
                    'nilai'              => (int) $val,
                ]);
            }
        });
         event(new \App\Events\NilaiSubmitted($lomba->event_id, "Nilai baru masuk: {$lomba->nama}"));
        return back()
            ->with('success', "Nilai regu #{$alokasi->nomor_urut_tampil} tersimpan & terkunci (rata-rata {$rata}).")
            ->setStatusCode(303);
    }

    public function rekap()
{
    $penilaians = Penilaian::where('juri_id', Auth::id())
        ->with(['lomba.event', 'kontingen.team'])
        ->orderByDesc('submitted_at')
        ->get();

    // ✅ Kategori (PA/PI) hidup di alokasi (lomba_kontingen), bukan di penilaian.
    //    Ambil 1x lewat map composite "lomba_id-kontingen_id" (anti N+1).
    //    Defensif: kalau kolom `kategori` belum ada, map kosong -> halaman tetap jalan.
    $kategoriMap = collect();
    if (\Illuminate\Support\Facades\Schema::hasColumn((new LombaKontingen)->getTable(), 'kategori')) {
        $lombaIds = $penilaians->pluck('lomba_id')->unique();
        $kategoriMap = LombaKontingen::whereIn('lomba_id', $lombaIds)
            ->get(['lomba_id', 'kontingen_id', 'kategori'])
            ->mapWithKeys(fn ($a) => [$a->lomba_id . '-' . $a->kontingen_id => $a->kategori]);
    }

    $grouped = $penilaians->groupBy('lomba_id')->map(fn ($g) => [
        'lomba' => $g->first()->lomba,
        'items' => $g->map(fn ($p) => [
            'team_name'         => $p->kontingen->team->name ?? '-',
            'golongan'          => $p->golongan,
            'kategori'          => $kategoriMap[$p->lomba_id . '-' . $p->kontingen_id] ?? null, // ✅ baru
            'nomor_urut_tampil' => $p->nomor_urut_tampil,
            'nilai_akhir_juri'  => $p->nilai_akhir_juri,
            'submitted_at'      => optional($p->submitted_at)->format('d M Y H:i'),
        ])->values(),
    ])->values();

    return inertia('Juri/Penilaian/Rekap', compact('grouped'));
}
}