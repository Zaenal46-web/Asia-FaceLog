<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Integrasi\PushLogController;
use App\Http\Controllers\Integrasi\RawLogController;
use App\Http\Controllers\Integrasi\WebhookLogController;
use App\Http\Controllers\Master\FingerDeviceController;
use App\Http\Controllers\Master\FingerUserController;
use App\Http\Controllers\Master\KategoriKaryawanController;
use App\Http\Controllers\Master\KategoriShiftController;
use App\Http\Controllers\Master\KaryawanController;
use App\Http\Controllers\Master\ShiftMasterController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HolidayCalendarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Route
|--------------------------------------------------------------------------
| Route awal aplikasi.
| Jika sudah login → dashboard
| Jika belum login → login
|
*/

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| Semua route di bawah ini wajib login.
|
*/

Route::middleware(['auth'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Main App
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Shared Access
    |--------------------------------------------------------------------------
    | Bisa diakses:
    | - superadmin
    | - hrd_asia
    | - hrd_outsourcing
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:superadmin,hrd_asia,hrd_outsourcing'])->group(function () {
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/proses', [AbsensiController::class, 'proses'])
        ->middleware('role:superadmin')
        ->name('absensi.proses');

    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::get('/export/download-xlsx', [ExportController::class, 'downloadXlsx'])->name('export.download-xlsx');

    Route::get('/holiday', [HolidayCalendarController::class, 'index'])->name('holiday.index');
    Route::get('/holiday/create', [HolidayCalendarController::class, 'create'])->name('holiday.create');
    Route::post('/holiday', [HolidayCalendarController::class, 'store'])->name('holiday.store');
    Route::get('/holiday/{holiday}/edit', [HolidayCalendarController::class, 'edit'])->name('holiday.edit');
    Route::put('/holiday/{holiday}', [HolidayCalendarController::class, 'update'])->name('holiday.update');
    Route::delete('/holiday/{holiday}', [HolidayCalendarController::class, 'destroy'])->name('holiday.destroy');
    //Route::view('/holiday', 'holiday.index')->name('holiday.index');
});

    /*
    |--------------------------------------------------------------------------
    | Superadmin Only
    |--------------------------------------------------------------------------
    | Semua master data sensitif + integrasi full.
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:superadmin'])->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        Route::resource('/master/karyawan', KaryawanController::class)
            ->names('master.karyawan')
            ->except(['show']);

        Route::resource('/master/kategori-karyawan', KategoriKaryawanController::class)
            ->names('master.kategori-karyawan')
            ->except(['show']);

        Route::resource('/master/device', FingerDeviceController::class)
            ->names('master.device')
            ->parameters(['device' => 'device'])
            ->except(['show']);

        Route::resource('/master/shift-master', ShiftMasterController::class)
            ->names('master.shift-master')
            ->except(['show']);

        Route::resource('/master/kategori-shift', KategoriShiftController::class)
            ->names('master.kategori-shift')
            ->except(['show']);

        Route::resource('/master/user-mesin', FingerUserController::class)
            ->names('master.user-mesin')
            ->parameters(['user-mesin' => 'userMesin'])
            ->except(['show']);

        Route::get('/master/user-mesin/{userMesin}/mutasi', [FingerUserController::class, 'showMutasiForm'])
            ->name('master.user-mesin.mutasi-form');

        Route::post('/master/user-mesin/{userMesin}/mutasi', [FingerUserController::class, 'mutasiDevice'])
            ->name('master.user-mesin.mutasi-device');

        Route::get('/master/user-mesin/{userMesin}/mutasi', [FingerUserController::class, 'showMutasiForm'])
            ->name('master.user-mesin.mutasi-form');

        Route::post('/master/user-mesin/{userMesin}/mutasi', [FingerUserController::class, 'mutasiDevice'])
            ->name('master.user-mesin.mutasi-device');

        Route::get('/master/user-mesin/{userMesin}/set-userinfo', [FingerUserController::class, 'showSetUserinfoForm'])
            ->name('master.user-mesin.set-userinfo-form');

        Route::post('/master/user-mesin/{userMesin}/set-userinfo', [FingerUserController::class, 'submitSetUserinfo'])
            ->name('master.user-mesin.set-userinfo-submit');

        /*
        |--------------------------------------------------------------------------
        | Device Actions (Outbound to Fingerspot API)
        |--------------------------------------------------------------------------
        */

        Route::post('/master/device/{device}/get-all-pin', [FingerDeviceController::class, 'getAllPinFromApi'])
            ->name('master.device.get-all-pin');

        Route::post('/master/device/{device}/set-time', [FingerDeviceController::class, 'setTimeToDevice'])
            ->name('master.device.set-time');

        Route::post('/master/device/{device}/get-attlog', [FingerDeviceController::class, 'getAttlogFromApi'])
            ->name('master.device.get-attlog');

        /*
        |--------------------------------------------------------------------------
        | User Mesin Actions (Outbound to Fingerspot API)
        |--------------------------------------------------------------------------
        */

        Route::post('/master/user-mesin/{userMesin}/request-userinfo', [FingerUserController::class, 'requestUserinfo'])
            ->name('master.user-mesin.request-userinfo');

        Route::post('/master/user-mesin/{userMesin}/push-set-userinfo', [FingerUserController::class, 'pushSetUserinfo'])
            ->name('master.user-mesin.push-set-userinfo');

        Route::post('/master/user-mesin/{userMesin}/delete-from-device', [FingerUserController::class, 'deleteFromDevice'])
            ->name('master.user-mesin.delete-from-device');

        Route::post('/master/user-mesin/{userMesin}/sync-to-karyawan', [FingerUserController::class, 'syncToKaryawan'])
            ->name('master.user-mesin.sync-to-karyawan');

        Route::post('/master/user-mesin/get-userinfo-massal', [FingerUserController::class, 'getUserinfoMassal'])
            ->name('master.user-mesin.get-userinfo-massal');

        Route::post('/master/user-mesin/sync-massal-to-karyawan', [FingerUserController::class, 'syncMassalToKaryawan'])
            ->name('master.user-mesin.sync-massal-to-karyawan');

        

        /*
        |--------------------------------------------------------------------------
        | Integration Audit
        |--------------------------------------------------------------------------
        */

        Route::resource('/integrasi/raw-log', RawLogController::class)
            ->names('integrasi.raw-log')
            ->parameters(['raw-log' => 'rawLog']);

        Route::resource('/integrasi/webhook-log', WebhookLogController::class)
            ->names('integrasi.webhook-log')
            ->parameters(['webhook-log' => 'webhookLog']);

        Route::resource('/integrasi/push-log', PushLogController::class)
            ->names('integrasi.push-log')
            ->parameters(['push-log' => 'pushLog']);
    });

    /*
    |--------------------------------------------------------------------------
    | User Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Breeze / Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';