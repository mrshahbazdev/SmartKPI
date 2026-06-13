<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentDashboardController;
use App\Http\Controllers\HoldingDashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\KpiDefinitionController;
use App\Http\Controllers\KpiValueController;
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

    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Level-specific Dashboards
    Route::get('/dashboard/department/{department}', [DepartmentDashboardController::class, 'show'])->name('dashboard.department');
    Route::get('/dashboard/company/{company}', [CompanyDashboardController::class, 'show'])->name('dashboard.company');
    Route::get('/dashboard/holding/{tenant}', [HoldingDashboardController::class, 'show'])->name('dashboard.holding');

    // KPI Definitions
    Route::get('/kpis', [KpiDefinitionController::class, 'index'])->name('kpis.index');
    Route::get('/kpis/create', [KpiDefinitionController::class, 'create'])->name('kpis.create');
    Route::post('/kpis', [KpiDefinitionController::class, 'store'])->name('kpis.store');
    Route::get('/kpis/templates', [KpiDefinitionController::class, 'templates'])->name('kpis.templates');
    Route::post('/kpis/templates/{template}/use', [KpiDefinitionController::class, 'useTemplate'])->name('kpis.use-template');
    Route::get('/kpis/{kpi}', [KpiDefinitionController::class, 'show'])->name('kpis.show');
    Route::get('/kpis/{kpi}/edit', [KpiDefinitionController::class, 'edit'])->name('kpis.edit');
    Route::put('/kpis/{kpi}', [KpiDefinitionController::class, 'update'])->name('kpis.update');
    Route::delete('/kpis/{kpi}', [KpiDefinitionController::class, 'destroy'])->name('kpis.destroy');

    // KPI Values (data entry)
    Route::post('/kpis/{kpi}/values', [KpiValueController::class, 'store'])->name('kpi-values.store');
    Route::post('/kpis/{kpi}/values/bulk', [KpiValueController::class, 'bulkStore'])->name('kpi-values.bulk');
    Route::post('/kpis/{kpi}/values/import', [KpiValueController::class, 'import'])->name('kpi-values.import');
    Route::put('/kpi-values/{value}', [KpiValueController::class, 'update'])->name('kpi-values.update');
    Route::delete('/kpi-values/{value}', [KpiValueController::class, 'destroy'])->name('kpi-values.destroy');

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
