<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PesertaDidikController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\KodeController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\BackupController;
use Barryvdh\DomPDF\Facade\Pdf;

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'showLoginForm')->name('login');
    Route::post('login', 'login')->name('login.process')->middleware('throttle:5,1');
    Route::post('logout', 'logout')->name('logout');
});

/*
|--------------------------------------------------------------------------
| AREA TERLINDUNG (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Profil user yang sedang login
    Route::get('/profil', [UserController::class, 'profil'])->name('profil');
    Route::put('/profil', [UserController::class, 'updateProfil'])->name('profil.update');

    // AI Chat Support
    Route::get('/chat/csrf-token', fn() => response()->json(['token' => csrf_token()]))->name('chat.csrf');
    Route::post('/chat/ask', [ChatController::class, 'ask'])->name('chat.ask')->middleware('throttle:30,1');
    Route::post('/chat/upload', [ChatController::class, 'uploadFile'])->name('chat.upload')->middleware('throttle:10,1');
    Route::get('/chat/rekap',         [ChatController::class, 'rekapBulanan'])->name('chat.rekap');
    Route::get('/chat/rekap-tahunan', [ChatController::class, 'rekapTahunan'])->name('chat.rekap.tahunan');



    /*
    |--------------------------------------------------------------------------
    | KODE SURAT
    |--------------------------------------------------------------------------
    */
    Route::prefix('kode')->name('kode.')->controller(KodeController::class)->group(function () {
        Route::get('/check-duplicate', 'checkDuplicate')->name('checkDuplicate');
        Route::get('/list', 'list')->name('list');
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{kode}', 'show')->name('show');
        Route::put('/{kode}', 'update')->name('update');
        Route::delete('/{kode}', 'destroy')->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->name('user.')->controller(UserController::class)->middleware('role:Kepala Staf')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}', 'show')->name('show');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | SISWA
    |--------------------------------------------------------------------------
    */
    Route::prefix('peserta-didik')->name('peserta-didik.')->controller(PesertaDidikController::class)->group(function () {

        // Harus di atas route /{peserta_didik} agar tidak tertangkap sebagai parameter
        Route::get('/cek-duplikat', 'cekDuplikat')->name('cekDuplikat');
        Route::post('/ai-search', 'aiSearch')->name('aiSearch');

        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');

        Route::get('/{peserta_didik}', 'show')->name('show');
        Route::put('/{peserta_didik}', 'update')->name('update');
        Route::delete('/{peserta_didik}', 'destroy')->name('destroy');

        Route::get('/{peserta_didik}/download/{field}', 'download')->name('download');
        Route::get('/{peserta_didik}/download-all', 'downloadAll')->name('downloadAll');
        Route::get('/{peserta_didik}/download-selected', 'downloadSelected')->name('downloadSelected');
    });

    /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */
    Route::prefix('role')->name('role.')->controller(RoleController::class)->middleware('role:Kepala Staf')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}', 'show')->name('show');
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | SURAT
    |--------------------------------------------------------------------------
    */
    Route::prefix('surat')->name('surat.')->controller(SuratController::class)->group(function () {
        Route::get('/kode-surat-keluar', 'kodeSuratKeluar')->name('kodeSuratKeluar');
        Route::get('/generate-nomor', 'generateNomor')->name('generateNomor');
        Route::get('/cek-duplikat', 'cekDuplikat')->name('cekDuplikat');
        Route::post('/ai-search', 'aiSearch')->name('aiSearch');

        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');

        // Clone dari halaman Surat, tetapi terkunci hanya menampilkan
        // jenis tertentu. Harus dideklarasikan SEBELUM wildcard /{surat}
        // agar tidak tertangkap oleh route show.
        Route::get('/masuk', 'masuk')->name('masuk');
        Route::get('/keluar', 'keluar')->name('keluar');

        Route::post('/download-multiple', 'downloadMultiple')->name('downloadMultiple');

        Route::get('/{surat}', 'show')->name('show');
        Route::put('/{surat}', 'update')->name('update');
        Route::delete('/{surat}', 'destroy')->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | LAPORAN REKAPITULASI
    |--------------------------------------------------------------------------
    */
    Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
        Route::get('/', 'filter')->name('filter');
        Route::get('/generate', 'generate')->name('generate');
        Route::get('/export', 'export')->name('export');
        Route::get('/print', 'printPreview')->name('print');
    });

    // Test PDF
    Route::get('/test-pdf', function () {
        $pdf = Pdf::loadHTML('<h1>Hello PDF</h1>');
        return $pdf->stream('test.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | BACKUP DATA (Kepala Staf only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('backup')->name('backup.')->controller(BackupController::class)
        ->middleware('role:Kepala Staf')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/generate', 'generate')->name('generate');
            Route::get('/download/{token}', 'download')->name('download');
        });
});