<?php

namespace App\Http\Controllers\Sekolah;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Kontingen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PendaftaranController extends Controller
{
    /**
     * Pangkalan milik operator yang sedang login (1 akun = 1 pangkalan).
     */
    private function pangkalan(Request $request)
    {
        return $request->user()->ownedTeams()->first();
    }

    /**
     * Scope reusable untuk event yang sedang aktif & buka pendaftaran.
     */
    private function activeEventsQuery(): Builder
    {
        return Event::query()
            ->where('status', 'aktif')
            ->whereDate('periode_pendaftaran_mulai', '<=', now())
            ->whereDate('periode_pendaftaran_selesai', '>=', now());
    }

    public function index(Request $request)
    {
        $pangkalan = $this->pangkalan($request);
        $pangkalanId = $pangkalan?->id;

        // Event yang sedang buka pendaftaran & belum didaftar pangkalan ini
        $availableEvents = $this->activeEventsQuery()
            ->whereDoesntHave('kontingens', fn ($q) => $q->where('team_id', $pangkalanId))
            ->orderBy('nama')
            ->get();

        // Riwayat pendaftaran pangkalan ini
        $myKontingens = Kontingen::with('event')
            ->where('team_id', $pangkalanId)
            ->latest()
            ->get();

        return inertia('Sekolah/Pendaftaran/Index', compact(
            'availableEvents',
            'myKontingens',
            'pangkalan'
        ));
    }

    public function create(Request $request)
    {
        $pangkalan = $this->pangkalan($request);
        abort_unless((bool) $pangkalan, 403, 'Pangkalan tidak ditemukan.');

        $event = $this->activeEventsQuery()
            ->where('id', $request->query('event_id'))
            ->firstOrFail();

        // Cegah daftar dobel
        if ($this->sudahTerdaftar($event->id, $pangkalan->id)) {
            return redirect()
                ->route('sekolah.pendaftaran.index')
                ->with('error', 'Pangkalan sudah terdaftar di event ini.');
        }

        return inertia('Sekolah/Pendaftaran/Form', compact('event', 'pangkalan'));
    }

    public function store(Request $request)
    {
        $pangkalan = $this->pangkalan($request);
        abort_unless((bool) $pangkalan, 403, 'Pangkalan tidak ditemukan.');

        $data = $request->validate([
            'event_id' => ['required', Rule::exists('events', 'id')],
            'nama_kontingen' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'setuju' => ['accepted'],
        ]);

        // Validasi event masih buka pendaftaran
        $event = $this->activeEventsQuery()
            ->where('id', $data['event_id'])
            ->firstOrFail();

        // Cegah daftar dobel
        if ($this->sudahTerdaftar($event->id, $pangkalan->id)) {
            return back()->with('error', 'Pangkalan sudah terdaftar di event ini.');
        }

        Kontingen::create([
            'event_id' => $event->id,
            'team_id' => $pangkalan->id,
            'status' => 'draft',
            'nama_kontingen' => $data['nama_kontingen'] ?: $pangkalan->name,
            'contact_person' => $data['contact_person'] ?: $request->user()->name,
            'contact_phone' => $data['contact_phone'] ?: ($pangkalan->no_telp ?? null),
        ]);

        return redirect()
            ->route('sekolah.pendaftaran.index')
            ->with('success', 'Kesediaan berhasil disimpan. Tahap berikutnya: upload bukti pembayaran.')
            ->setStatusCode(303);
    }

    public function editBayar(Request $request, Kontingen $kontingen)
    {
        $this->ensureOwnKontingen($request, $kontingen);

        // Cuma boleh upload kalau belum diproses / ditolak
        abort_unless(
            in_array($kontingen->status, ['draft', 'pembayaran_ditolak']),
            403,
            'Pembayaran sudah diproses.'
        );

        $kontingen->load('event');
        $media = $kontingen->getFirstMedia('bukti_pembayaran');

        return inertia('Sekolah/Pendaftaran/Bayar', [
            'kontingen' => $kontingen,
            'bukti' => $media ? [
                'url' => $media->getUrl(),
                'mime' => $media->mime_type,
                'name' => $media->file_name,
            ] : null,
        ]);
    }

    public function uploadBayar(Request $request, Kontingen $kontingen)
    {
        $this->ensureOwnKontingen($request, $kontingen);

        abort_unless(
            in_array($kontingen->status, ['draft', 'pembayaran_ditolak']),
            403,
            'Pembayaran sudah diproses.'
        );

        $request->validate([
            'bukti_pembayaran' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        // singleFile() pada Media Library otomatis replace file lama
        $kontingen
            ->addMediaFromRequest('bukti_pembayaran')
            ->toMediaCollection('bukti_pembayaran');

        $kontingen->update([
            'status' => 'menunggu_approval_pembayaran',
        ]);

        return redirect()
            ->route('sekolah.pendaftaran.index')
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu approval Admin.')
            ->setStatusCode(303);
    }

    /**
     * Cek apakah pangkalan sudah terdaftar pada event tertentu.
     */
    private function sudahTerdaftar(int $eventId, int $pangkalanId): bool
    {
        return Kontingen::where('event_id', $eventId)
            ->where('team_id', $pangkalanId)
            ->exists();
    }

    /**
     * Pastikan kontingen milik pangkalan operator yang sedang login.
     */
    private function ensureOwnKontingen(Request $request, Kontingen $kontingen): void
    {
        $pangkalan = $this->pangkalan($request);

        abort_unless(
            $pangkalan && $kontingen->team_id === $pangkalan->id,
            403,
            'Kontingen bukan milik pangkalan Anda.'
        );
    }
}