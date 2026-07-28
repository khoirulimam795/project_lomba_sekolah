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
    private function pangkalan(Request $request)
    {
        return $request->user()->ownedTeams()->first();
    }

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

        $availableEvents = $this->activeEventsQuery()
            ->whereDoesntHave('kontingens', fn ($q) => $q->where('team_id', $pangkalanId))
            ->orderBy('nama')
            ->get();

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

        // ✅ VALIDASI FINAL — tanpa duplikat, tanpa 'setuju'
        $data = $request->validate([
            'event_id'             => ['required', Rule::exists('events', 'id')],
            'nama_kontingen'       => ['nullable', 'string', 'max:255'],
            'nama_kepala_madrasah' => ['required', 'string', 'max:255'],
            'asal_instansi'        => ['nullable', 'string', 'max:255'],
            'contact_person'       => ['required', 'string', 'max:255'],
            'contact_phone'        => ['required', 'string', 'max:30'],
            'pendamping_putra'     => ['required', 'integer', 'min:0', 'max:20'],
            'pendamping_putri'     => ['required', 'integer', 'min:0', 'max:20'],
            'peserta_putra'        => ['required', 'integer', 'min:0', 'max:50'],
            'peserta_putri'        => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $event = $this->activeEventsQuery()
            ->where('id', $data['event_id'])
            ->firstOrFail();

        if ($this->sudahTerdaftar($event->id, $pangkalan->id)) {
            return back()->with('error', 'Pangkalan sudah terdaftar di event ini.');
        }

        // ✅ SIMPAN SEMUA KOLOM C.01
        Kontingen::create([
            'event_id'              => $event->id,
            'team_id'               => $pangkalan->id,
            'status'                => 'draft',
            'nama_kontingen'        => $data['nama_kontingen'] ?: $pangkalan->name,
            'nama_kepala_madrasah'  => $data['nama_kepala_madrasah'],
            'asal_instansi'         => $data['asal_instansi'] ?? $pangkalan->name,
            'contact_person'        => $data['contact_person'],
            'contact_phone'         => $data['contact_phone'],
            'pendamping_putra'      => $data['pendamping_putra'],
            'pendamping_putri'      => $data['pendamping_putri'],
            'peserta_putra'         => $data['peserta_putra'],
            'peserta_putri'         => $data['peserta_putri'],
        ]);

        return redirect()
            ->route('sekolah.pendaftaran.index')
            ->with('success', 'Formulir kesediaan berhasil disimpan. Tahap berikutnya: upload bukti pembayaran.')
            ->setStatusCode(303);
    }

    public function editBayar(Request $request, Kontingen $kontingen)
    {
        $this->ensureOwnKontingen($request, $kontingen);

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
                'url'  => $media->getUrl(),
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

    private function sudahTerdaftar(int $eventId, int $pangkalanId): bool
    {
        return Kontingen::where('event_id', $eventId)
            ->where('team_id', $pangkalanId)
            ->exists();
    }

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