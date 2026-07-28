<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Juri;
use App\Models\Kontingen;
use App\Models\Team;
use App\Models\Lomba;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return inertia('Admin/Dashboard', [
            'stats' => [
                'events'     => Event::count(),
                'lombas'     => Lomba::count(),
                'juris'      => User::role('juri')->count(),
                'kontingens' => Kontingen::count(),
                'peserta'    => \App\Models\Siswa::count(),
                'pendamping' => \App\Models\Pendamping::count(),
                'terverifikasi' => Kontingen::where('status', 'terverifikasi')->count(),
                'menunggu_pembayaran' => Kontingen::where('status', 'menunggu_approval_pembayaran')->count(),
            ],
        ]);
    }
}