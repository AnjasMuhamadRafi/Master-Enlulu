<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsUserController;
use App\Http\Controllers\LogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
});

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Employee Management
    Route::prefix('employee')->name('employee.')->group(function () {
        // Custom routes HARUS DULU sebelum /{employee}
        Route::get('/template/download', [EmployeeController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/import', [EmployeeController::class, 'showImportForm'])->name('import');
        Route::post('/import-validate', [EmployeeController::class, 'importValidate'])->name('import-validate');
        Route::post('/import-process', [EmployeeController::class, 'import'])->name('import-process');
        Route::get('/export', [EmployeeController::class, 'export'])->name('export');
        Route::get('/report', [EmployeeController::class, 'report'])->name('report');
        
        // CRUD routes (harus SETELAH custom routes)
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
    });
    
    // Contract Management
    Route::prefix('contract')->name('contract.')->group(function () {
        Route::get('/', function () { return view('contract.index'); })->name('index');
        Route::get('/create', function () { return view('contract.create'); })->name('create');
        Route::post('/', function () { return back(); })->name('store');
        Route::get('/{id}/edit', function () { return view('contract.edit'); })->name('edit');
        Route::put('/{id}', function () { return back(); })->name('update');
        Route::delete('/{id}', function () { return back(); })->name('destroy');
        Route::get('/template', function () { return view('contract.template'); })->name('template');
    });
    
    // Reports
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('/aktif', [ReportController::class, 'aktif'])->name('aktif');
        Route::get('/training', [ReportController::class, 'training'])->name('training');
        Route::get('/resign', [ReportController::class, 'resign'])->name('resign');
        Route::get('/fraud', [ReportController::class, 'fraud'])->name('fraud');
    });
    
    // Settings - Super Admin & Admin only
    Route::middleware('admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/users', [SettingsUserController::class, 'index'])->name('users');
        Route::get('/roles', function () { return view('settings.roles'); })->name('roles');
        Route::post('/users', [SettingsUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [SettingsUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [SettingsUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [SettingsUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/profile', function () { return view('settings.profile'); })->name('profile');
        Route::get('/security', function () { return view('settings.security'); })->name('security');
        Route::put('/security', [SettingsUserController::class, 'updateSecurity'])->name('security.update');
    });
    
    // Logs - Super Admin only
    Route::get('/logs', [LogController::class, 'index'])->name('log.index');
});
