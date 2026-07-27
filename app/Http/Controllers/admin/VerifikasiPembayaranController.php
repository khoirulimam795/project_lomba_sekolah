<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kontingen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifikasiPembayaranController extends Controller
{
    public function index()
    {
        $kontingens = Kontingen::with(['event', 'team'])
            ->where('status', 'menunggu_approval_pembayaran')
            ->latest()
            ->get();

        return inertia('Admin/VerifikasiPembayaran/Index', compact('kontingens'));
    }

    public function show(Kontingen $kontingen)
    {
        $kontingen->load(['event', 'team']);

        $media = $kontingen->getFirstMedia('bukti_pembayaran');

        return inertia('Admin/VerifikasiPembayaran/Show', [
            'kontingen' => $kontingen,
            'bukti' => $media ? [
                'url'  => $media->getUrl(),
                'mime' => $media->mime_type,
                'name' => $media->file_name,
            ] : null,
        ]);
    }

    public function approve(Kontingen $kontingen)
    {
        abort_unless(
            $kontingen->status === 'menunggu_approval_pembayaran',
            400,
            'Status kontingen tidak valid untuk approval.'
        );

        $kontingen->update([
            'status'             => 'menunggu_verifikasi_dokumen',
            'approved_by'        => Auth::id(),
            'approved_at'        => now(),
            'catatan_pembayaran' => null,
        ]);

        return redirect()
            ->route('admin.verifikasi-pembayaran.index')
            ->with('success', 'Pembayaran disetujui. Operator kini dapat mengisi biodata.')
            ->setStatusCode(303);
    }

    public function reject(Request $request, Kontingen $kontingen)
    {
        abort_unless(
            $kontingen->status === 'menunggu_approval_pembayaran',
            400,
            'Status kontingen tidak valid untuk penolakan.'
        );

        $data = $request->validate([
            'catatan_pembayaran' => ['required', 'string', 'max:1000'],
        ]);

        $kontingen->update([
            'status'             => 'pembayaran_ditolak',
            'catatan_pembayaran' => $data['catatan_pembayaran'],
        ]);

        return redirect()
            ->route('admin.verifikasi-pembayaran.index')
            ->with('success', 'Pembayaran ditolak. Operator diminta upload ulang.')
            ->setStatusCode(303);
    }
}