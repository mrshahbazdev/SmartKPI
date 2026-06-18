<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

// Locale switching
Route::get('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// Invitation routes
Route::get('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
Route::post('/invitation/{token}', [InvitationController::class, 'register'])->name('invitation.register');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Analysis
    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis');

    // Organizations
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::post('/organizations/companies', [OrganizationController::class, 'storeCompany'])->name('companies.store');
    Route::put('/organizations/companies/{company}', [OrganizationController::class, 'updateCompany'])->name('companies.update');
    Route::delete('/organizations/companies/{company}', [OrganizationController::class, 'destroyCompany'])->name('companies.destroy');
    Route::post('/organizations/departments', [OrganizationController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/organizations/departments/{department}', [OrganizationController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/organizations/departments/{department}', [OrganizationController::class, 'destroyDepartment'])->name('departments.destroy');

    // Invitations
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
});
