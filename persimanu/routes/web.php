<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\JuriController;
use App\Http\Controllers\Admin\KriteriaController;
use App\Http\Controllers\Admin\LombaController;
use App\Http\Controllers\Admin\NomorUrutController;
use App\Http\Controllers\Admin\PenugasanJuriController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Admin\VerifikasiPembayaranController;
use App\Http\Controllers\Juri\PenilaianController;
use App\Http\Controllers\Publik\LandingController;
use App\Http\Controllers\Sekolah\AlokasiController;
use App\Http\Controllers\Sekolah\PendaftaranController;
use App\Http\Controllers\Sekolah\PendampingController;
use App\Http\Controllers\Sekolah\SiswaController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ===== LANDING PUBLIK (root = etalase, bukan redirect login) =====
Route::get('/', [LandingController::class, 'index'])->name('publik.landing');
Route::get('/event/{event:slug}', [LandingController::class, 'show'])->name('publik.event');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        if ($user?->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user?->hasRole('juri')) {
            return redirect()->route('juri.dashboard');
        }
        if ($user?->hasRole('operator-sekolah')) {
            return redirect()->route('sekolah.pendaftaran.index');
        }

        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// ===== ADMIN =====
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:admin'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('events', EventController::class)->except(['show']);
        Route::resource('juris', JuriController::class)->except(['show']);
        Route::resource('lombas', LombaController::class)->except(['show']);
        Route::get('kriterias', [KriteriaController::class, 'index'])->name('kriterias.index');
        Route::get('kriterias/create', [KriteriaController::class, 'create'])->name('kriterias.create');
        Route::post('kriterias', [KriteriaController::class, 'store'])->name('kriterias.store');
        Route::get('kriterias/{lomba}/edit', [KriteriaController::class, 'edit'])->name('kriterias.edit');
        Route::put('kriterias/{lomba}', [KriteriaController::class, 'update'])->name('kriterias.update');
        Route::delete('kriterias/{kriteriaKomponen}', [KriteriaController::class, 'destroy'])->name('kriterias.destroy');
        Route::get('penugasan-juri', [PenugasanJuriController::class, 'index'])->name('penugasan-juri.index');
        Route::get('penugasan-juri/{lomba}', [PenugasanJuriController::class, 'edit'])->name('penugasan-juri.edit');
        Route::put('penugasan-juri/{lomba}', [PenugasanJuriController::class, 'update'])->name('penugasan-juri.update');
        Route::get('verifikasi-pembayaran', [VerifikasiPembayaranController::class, 'index'])->name('verifikasi-pembayaran.index');
        Route::get('verifikasi-pembayaran/{kontingen}', [VerifikasiPembayaranController::class, 'show'])->name('verifikasi-pembayaran.show');
        Route::post('verifikasi-pembayaran/{kontingen}/approve', [VerifikasiPembayaranController::class, 'approve'])->name('verifikasi-pembayaran.approve')->middleware('throttle:30,1');
        Route::post('verifikasi-pembayaran/{kontingen}/reject', [VerifikasiPembayaranController::class, 'reject'])->name('verifikasi-pembayaran.reject')->middleware('throttle:30,1');
        Route::get('verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('verifikasi/siswa/{siswa}/approve', [VerifikasiController::class, 'approveSiswa'])->name('verifikasi.siswa.approve');
        Route::post('verifikasi/siswa/{siswa}/reject', [VerifikasiController::class, 'rejectSiswa'])->name('verifikasi.siswa.reject');
        Route::post('verifikasi/pendamping/{pendamping}/approve', [VerifikasiController::class, 'approvePendamping'])->name('verifikasi.pendamping.approve');
        Route::post('verifikasi/pendamping/{pendamping}/reject', [VerifikasiController::class, 'rejectPendamping'])->name('verifikasi.pendamping.reject');
        Route::get('verifikasi/{kontingen}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::get('nomor-urut', [NomorUrutController::class, 'index'])->name('nomor-urut.index');
        Route::put('nomor-urut', [NomorUrutController::class, 'save'])->name('nomor-urut.save');
        Route::post('nomor-urut/{alokasi}/lock', [NomorUrutController::class, 'lock'])->name('nomor-urut.lock');
        Route::post('nomor-urut/{alokasi}/unlock', [NomorUrutController::class, 'unlock'])->name('nomor-urut.unlock');
        // URUTAN PENTING: juara-umum SEBELUM {lomba}
        Route::get('rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('rekap/juara-umum', [RekapController::class, 'juaraUmum'])->name('rekap.juara-umum');
        Route::get('rekap/{lomba}', [RekapController::class, 'show'])->name('rekap.show');
        Route::post('rekap/{lomba}/finalize', [RekapController::class, 'finalize'])->name('rekap.finalize');
        Route::get('rekap/{lomba}/edit-nilai/{kontingen}/{golongan}', [RekapController::class, 'editNilai'])->name('rekap.edit-nilai');
        Route::put('rekap/{lomba}/edit-nilai/{penilaian}', [RekapController::class, 'updateNilai'])->name('rekap.update-nilai');
        Route::get('rekap/{lomba}/export/excel', [RekapController::class, 'exportExcel'])->name('rekap.export-excel');
        Route::get('rekap/{lomba}/export/csv', [RekapController::class, 'exportCsv'])->name('rekap.export-csv');
    });

// ===== JURI (penilaian WAJIB di sini) =====
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:juri'])
    ->prefix('juri')->name('juri.')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('Juri/Dashboard'))->name('dashboard');
        Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('/penilaian/{lomba}', [PenilaianController::class, 'show'])->name('penilaian.show');
        Route::post('/penilaian/{lomba}/{alokasi}', [PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('/rekap', [PenilaianController::class, 'rekap'])->name('penilaian.rekap');
    });

// ===== OPERATOR / PESERTA =====
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:operator-sekolah'])
    ->prefix('sekolah')->name('sekolah.')->group(function () {
        Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
        Route::get('/pendaftaran/create', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
        Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
        Route::get('/pendaftaran/{kontingen}/bayar', [PendaftaranController::class, 'editBayar'])->name('pendaftaran.bayar');
        Route::post('/pendaftaran/{kontingen}/bayar', [PendaftaranController::class, 'uploadBayar'])->name('pendaftaran.bayar.upload')->middleware('throttle:10,1');
        Route::get('/kontingen/{kontingen}/siswa', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('/kontingen/{kontingen}/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
        Route::post('/kontingen/{kontingen}/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::get('/kontingen/{kontingen}/siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('/kontingen/{kontingen}/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('/kontingen/{kontingen}/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
        Route::get('/kontingen/{kontingen}/pendamping', [PendampingController::class, 'index'])->name('pendamping.index');
        Route::get('/kontingen/{kontingen}/pendamping/create', [PendampingController::class, 'create'])->name('pendamping.create');
        Route::post('/kontingen/{kontingen}/pendamping', [PendampingController::class, 'store'])->name('pendamping.store');
        Route::get('/kontingen/{kontingen}/pendamping/{pendamping}/edit', [PendampingController::class, 'edit'])->name('pendamping.edit');
        Route::put('/kontingen/{kontingen}/pendamping/{pendamping}', [PendampingController::class, 'update'])->name('pendamping.update');
        Route::delete('/kontingen/{kontingen}/pendamping/{pendamping}', [PendampingController::class, 'destroy'])->name('pendamping.destroy');
        Route::get('/kontingen/{kontingen}/alokasi', [AlokasiController::class, 'index'])->name('alokasi.index');
        Route::get('/kontingen/{kontingen}/alokasi/create', [AlokasiController::class, 'create'])->name('alokasi.create');
        Route::post('/kontingen/{kontingen}/alokasi', [AlokasiController::class, 'store'])->name('alokasi.store');
        Route::get('/kontingen/{kontingen}/alokasi/{alokasi}/edit', [AlokasiController::class, 'edit'])->name('alokasi.edit');
        Route::put('/kontingen/{kontingen}/alokasi/{alokasi}', [AlokasiController::class, 'update'])->name('alokasi.update');
        Route::delete('/kontingen/{kontingen}/alokasi/{alokasi}', [AlokasiController::class, 'destroy'])->name('alokasi.destroy');
    });
