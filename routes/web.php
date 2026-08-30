<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRecapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MyScheduleController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleInfoController;
use App\Http\Controllers\TeachingRateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Absensi & Dynamic Menu RBAC
|--------------------------------------------------------------------------
*/

// Redirect root ke login atau dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Autentikasi Publik
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('demo.login');
});

// Rute yang membutuhkan Login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:dashboard,view');

    // Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Manajemen Menu (Dynamic Menu Rules)
    Route::prefix('menus')->name('menus.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index')->middleware('permission:menus,view');
        Route::post('/', [MenuController::class, 'store'])->name('store')->middleware('permission:menus,create');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit')->middleware('permission:menus,update');
        Route::match(['put', 'post'], '/{menu}/update', [MenuController::class, 'update'])->name('update')->middleware('permission:menus,update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy')->middleware('permission:menus,delete');
        Route::post('/{menu}/toggle', [MenuController::class, 'toggleActive'])->name('toggle')->middleware('permission:menus,update');
    });

    // Manajemen Role & Permission Matrix
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:roles,view');
        Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('permission:roles,create');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('permission:roles,update');
        Route::match(['put', 'post'], '/{role}/update', [RoleController::class, 'update'])->name('update')->middleware('permission:roles,update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:roles,delete');
        Route::get('/{role}/permissions', [RoleController::class, 'permissions'])->name('permissions')->middleware('permission:roles,view');
        Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('permissions.update')->middleware('permission:roles,update');
    });

    // Manajemen Pengguna
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/export', [UserController::class, 'export'])->name('export')->middleware('permission:users,export');
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:users,view');
        Route::post('/', [UserController::class, 'store'])->name('store')->middleware('permission:users,create');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('permission:users,update');
        Route::match(['put', 'post'], '/{user}/update', [UserController::class, 'update'])->name('update')->middleware('permission:users,update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('permission:users,delete');
        Route::post('/{user}/toggle', [UserController::class, 'toggleActive'])->name('toggle')->middleware('permission:users,update');
    });

    // Jadwal Mengajar Saya (Perorangan / Personal)
    Route::prefix('my-schedule')->name('my-schedule.')->group(function () {
        Route::get('/', [MyScheduleController::class, 'index'])->name('index')->middleware('permission:my-schedule,view');
    });

    // Informasi Jadwal Mengajar Guru Yayasan (View & Monitoring)
    Route::prefix('schedule-info')->name('schedule-info.')->group(function () {
        Route::get('/export', [ScheduleInfoController::class, 'exportExcel'])->name('export')->middleware('permission:schedule-info,export');
        Route::get('/export-pdf', [ScheduleInfoController::class, 'exportPdf'])->name('export-pdf')->middleware('permission:schedule-info,export');
        Route::get('/{user}/export-pdf', [ScheduleInfoController::class, 'exportPersonalPdf'])->name('export-personal-pdf')->middleware('permission:schedule-info,export');
        Route::get('/', [ScheduleInfoController::class, 'index'])->name('index')->middleware('permission:schedule-info,view');
    });

    // Jam Kerja Pegawai & Master Unit Yayasan (TK, SD, SMP, SMA)
    Route::prefix('work-schedules')->name('work-schedules.')->group(function () {
        Route::get('/export', [WorkScheduleController::class, 'exportExcel'])->name('export')->middleware('permission:work-schedules,export');
        Route::get('/', [WorkScheduleController::class, 'index'])->name('index')->middleware('permission:work-schedules,view');
        Route::get('/{user}/edit', [WorkScheduleController::class, 'edit'])->name('edit')->middleware('permission:work-schedules,update');
        Route::match(['put', 'post'], '/{user}/update', [WorkScheduleController::class, 'updateEmployeeSchedule'])->name('update')->middleware('permission:work-schedules,update');
        Route::match(['put', 'post'], '/units/{unit}/update', [WorkScheduleController::class, 'updateUnit'])->name('units.update')->middleware('permission:work-schedules,update');
        Route::match(['put', 'post'], '/units/{unit}/schedules', [WorkScheduleController::class, 'updateUnitSchedule'])->name('units.schedules')->middleware('permission:work-schedules,update');
        Route::post('/bulk-assign', [WorkScheduleController::class, 'bulkAssign'])->name('bulk-assign')->middleware('permission:work-schedules,update');
    });

    // Data Absensi & Inject Excel
    Route::prefix('attendances')->name('attendances.')->group(function () {
        Route::get('/template', [AttendanceController::class, 'downloadTemplate'])->name('template')->middleware('permission:attendances,create');
        Route::post('/preview', [AttendanceController::class, 'previewExcel'])->name('preview')->middleware('permission:attendances,create');
        Route::post('/commit-import', [AttendanceController::class, 'commitImport'])->name('commit')->middleware('permission:attendances,create');
        Route::get('/export', [AttendanceController::class, 'exportExcel'])->name('export')->middleware('permission:attendances,export');
        Route::get('/', [AttendanceController::class, 'index'])->name('index')->middleware('permission:attendances,view');
        Route::post('/', [AttendanceController::class, 'store'])->name('store')->middleware('permission:attendances,create');
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit')->middleware('permission:attendances,update');
        Route::match(['put', 'post'], '/{attendance}/update', [AttendanceController::class, 'update'])->name('update')->middleware('permission:attendances,update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy')->middleware('permission:attendances,delete');
    });

    // Rekap Absen Per Pegawai
    Route::prefix('attendance-recap')->name('attendance-recap.')->group(function () {
        Route::get('/export', [AttendanceRecapController::class, 'exportExcel'])->name('export')->middleware('permission:attendance-recap,export');
        Route::get('/', [AttendanceRecapController::class, 'index'])->name('index')->middleware('permission:attendance-recap,view');
        Route::get('/{user}/detail-ajax', [AttendanceRecapController::class, 'detailAjax'])->name('detail.ajax')->middleware('permission:attendance-recap,view');
        Route::get('/{user}/chart-ajax', [AttendanceRecapController::class, 'chartDataAjax'])->name('chart.ajax')->middleware('permission:attendance-recap,view');
        Route::get('/{user}/chart', [AttendanceRecapController::class, 'chartView'])->name('chart')->middleware('permission:attendance-recap,view');
        Route::get('/{user}', [AttendanceRecapController::class, 'show'])->name('show')->middleware('permission:attendance-recap,view');
    });

    // Laporan Presensi & Analitik Kehadiran (Export Excel & PDF)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel')->middleware('permission:reports,export');
        Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf')->middleware('permission:reports,export');
        Route::get('/', [ReportController::class, 'index'])->name('index')->middleware('permission:reports,view');
    });

    // Master Tarif Honor Mengajar Per Jam (Jenjang & Mapel)
    Route::prefix('teaching-rates')->name('teaching-rates.')->group(function () {
        Route::get('/', [TeachingRateController::class, 'index'])->name('index')->middleware('permission:teaching-rates,view');
        Route::post('/', [TeachingRateController::class, 'store'])->name('store')->middleware('permission:teaching-rates,create');
        Route::get('/{teachingRate}/edit', [TeachingRateController::class, 'edit'])->name('edit')->middleware('permission:teaching-rates,update');
        Route::match(['put', 'post'], '/{teachingRate}/update', [TeachingRateController::class, 'update'])->name('update')->middleware('permission:teaching-rates,update');
        Route::delete('/{teachingRate}', [TeachingRateController::class, 'destroy'])->name('destroy')->middleware('permission:teaching-rates,delete');
        Route::post('/{teachingRate}/toggle', [TeachingRateController::class, 'toggleActive'])->name('toggle')->middleware('permission:teaching-rates,update');
    });

    // Penggajian Guru (Payroll & Honorarium)
    Route::prefix('payrolls')->name('payrolls.')->group(function () {
        Route::get('/export-summary-excel', [PayrollController::class, 'exportSummaryExcel'])->name('export-excel')->middleware('permission:payrolls,export');
        Route::get('/export-summary-pdf', [PayrollController::class, 'exportSummaryPdf'])->name('export-pdf')->middleware('permission:payrolls,export');
        Route::post('/generate', [PayrollController::class, 'generate'])->name('generate')->middleware('permission:payrolls,create');
        Route::get('/', [PayrollController::class, 'index'])->name('index')->middleware('permission:payrolls,view');
        Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show')->middleware('permission:payrolls,view');
        Route::post('/{payroll}/adjustments', [PayrollController::class, 'storeAdjustment'])->name('adjustments.store')->middleware('permission:payrolls,update');
        Route::delete('/{payroll}/adjustments/{adjustment}', [PayrollController::class, 'destroyAdjustment'])->name('adjustments.destroy')->middleware('permission:payrolls,update');
        Route::match(['put', 'post'], '/{payroll}/status', [PayrollController::class, 'updateStatus'])->name('status.update')->middleware('permission:payrolls,update');
        Route::post('/{payroll}/recalculate', [PayrollController::class, 'recalculate'])->name('recalculate')->middleware('permission:payrolls,update');
        Route::get('/{payroll}/slip-pdf', [PayrollController::class, 'exportPdf'])->name('slip-pdf')->middleware('permission:payrolls,export');
        Route::delete('/{payroll}', [PayrollController::class, 'destroy'])->name('destroy')->middleware('permission:payrolls,delete');
    });
});
