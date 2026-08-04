<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kontingen;
use App\Models\Pendamping;
use App\Models\Siswa;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function index()
    {
        $kontingens = Kontingen::with(['event', 'team'])
            ->whereIn('status', ['menunggu_verifikasi_dokumen', 'verifikasi_ditolak'])
            ->withCount([
                'siswas',
                'siswas as siswa_approved'           => fn ($q) => $q->where('status_verifikasi', 'approved'),
                'pendampings',
                'pendampings as pendamping_approved' => fn ($q) => $q->where('status_verifikasi', 'approved'),
            ])
            ->latest()
            ->get();

        return inertia('Admin/Verifikasi/Index', compact('kontingens'));
    }

    public function show(Kontingen $kontingen)
    {
        $kontingen->load(['event', 'team']);

        $siswas = $kontingen->siswas()->with('media')->orderBy('nama')->get()->map(function ($s) {
            $doc = $s->getFirstMedia('surat_kesehatan');
            return array_merge($s->toArray(), [
                'has_doc' => (bool) $doc,
                'surat_kesehatan' => $doc ? ['url' => $doc->getUrl(), 'mime' => $doc->mime_type, 'name' => $doc->file_name] : null,
            ]);
        });

        $pendampings = $kontingen->pendampings()->orderBy('nama')->get();

        return inertia('Admin/Verifikasi/Show', compact('kontingen', 'siswas', 'pendampings'));
    }

    public function approveSiswa(Siswa $siswa)
    {
        $kontingen = $siswa->kontingen;
        $siswa->update(['status_verifikasi' => 'approved', 'catatan_verifikasi' => null]);
        $kontingen->segelVerifikasi();
        return redirect()->route('admin.verifikasi.show', $kontingen)
            ->with('success', "Siswa \"{$siswa->nama}\" disetujui.")->setStatusCode(303);
    }

    public function rejectSiswa(Request $request, Siswa $siswa)
    {
        $data = $request->validate(['catatan_verifikasi' => ['required', 'string', 'max:1000']]);
        $kontingen = $siswa->kontingen;
        $siswa->update(['status_verifikasi' => 'rejected', 'catatan_verifikasi' => $data['catatan_verifikasi']]);
        $kontingen->segelVerifikasi();
        return redirect()->route('admin.verifikasi.show', $kontingen)
            ->with('success', "Siswa \"{$siswa->nama}\" ditolak.")->setStatusCode(303);
    }

    public function approvePendamping(Pendamping $pendamping)
    {
        $kontingen = $pendamping->kontingen;
        $pendamping->update(['status_verifikasi' => 'approved', 'catatan_verifikasi' => null]);
        $kontingen->segelVerifikasi();
        return redirect()->route('admin.verifikasi.show', $kontingen)
            ->with('success', "Pendamping \"{$pendamping->nama}\" disetujui.")->setStatusCode(303);
    }

    public function rejectPendamping(Request $request, Pendamping $pendamping)
    {
        $data = $request->validate(['catatan_verifikasi' => ['required', 'string', 'max:1000']]);
        $kontingen = $pendamping->kontingen;
        $pendamping->update(['status_verifikasi' => 'rejected', 'catatan_verifikasi' => $data['catatan_verifikasi']]);
        $kontingen->segelVerifikasi();
        return redirect()->route('admin.verifikasi.show', $kontingen)
            ->with('success', "Pendamping \"{$pendamping->nama}\" ditolak.")->setStatusCode(303);
    }
}