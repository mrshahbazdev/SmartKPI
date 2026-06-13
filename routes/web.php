<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\AlertRuleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\DailyFocusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentDashboardController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\HoldingDashboardController;
use App\Http\Controllers\HoldingIntelligenceController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\KpiDefinitionController;
use App\Http\Controllers\KpiRelationshipController;
use App\Http\Controllers\KpiValueController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\WebhookController;
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

    // Problems
    Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
    Route::get('/problems/timeline', [ProblemController::class, 'timeline'])->name('problems.timeline');
    Route::get('/problems/{problem}', [ProblemController::class, 'show'])->name('problems.show');
    Route::put('/problems/{problem}', [ProblemController::class, 'update'])->name('problems.update');
    Route::post('/problems/{problem}/assign', [ProblemController::class, 'assign'])->name('problems.assign');

    // Actions
    Route::get('/actions', [ActionController::class, 'index'])->name('actions.index');
    Route::get('/actions/create', [ActionController::class, 'create'])->name('actions.create');
    Route::post('/actions', [ActionController::class, 'store'])->name('actions.store');
    Route::get('/actions/{action}', [ActionController::class, 'show'])->name('actions.show');
    Route::put('/actions/{action}', [ActionController::class, 'update'])->name('actions.update');
    Route::delete('/actions/{action}', [ActionController::class, 'destroy'])->name('actions.destroy');

    // KPI Relationships
    Route::get('/relationships', [KpiRelationshipController::class, 'index'])->name('relationships.index');
    Route::post('/relationships', [KpiRelationshipController::class, 'store'])->name('relationships.store');
    Route::put('/relationships/{relationship}', [KpiRelationshipController::class, 'update'])->name('relationships.update');
    Route::delete('/relationships/{relationship}', [KpiRelationshipController::class, 'destroy'])->name('relationships.destroy');
    Route::get('/relationships/trace/{kpi}', [KpiRelationshipController::class, 'trace'])->name('relationships.trace');
    Route::post('/relationships/correlate', [KpiRelationshipController::class, 'correlate'])->name('relationships.correlate');

    // Alert Rules (company-specific triggers)
    Route::get('/alert-rules', [AlertRuleController::class, 'index'])->name('alert-rules.index');
    Route::post('/alert-rules', [AlertRuleController::class, 'store'])->name('alert-rules.store');
    Route::put('/alert-rules/{alertRule}', [AlertRuleController::class, 'update'])->name('alert-rules.update');
    Route::delete('/alert-rules/{alertRule}', [AlertRuleController::class, 'destroy'])->name('alert-rules.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Daily Focus
    Route::post('/daily-focus/generate', [DailyFocusController::class, 'generate'])->name('daily-focus.generate');

    // Holding Intelligence (Phase 4)
    Route::get('/holding/risk', [HoldingIntelligenceController::class, 'riskOverview'])->name('holding.risk');
    Route::get('/holding/cross-effects', [HoldingIntelligenceController::class, 'crossCompanyEffects'])->name('holding.cross-effects');
    Route::get('/holding/benchmark', [HoldingIntelligenceController::class, 'benchmark'])->name('holding.benchmark');

    // Forecasting
    Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast.index');
    Route::post('/forecast/generate', [ForecastController::class, 'generate'])->name('forecast.generate');
    Route::get('/forecast/warnings', [ForecastController::class, 'earlyWarnings'])->name('forecast.warnings');

    // Scenarios (What-if)
    Route::get('/scenarios', [ScenarioController::class, 'index'])->name('scenarios.index');
    Route::get('/scenarios/create', [ScenarioController::class, 'create'])->name('scenarios.create');
    Route::post('/scenarios', [ScenarioController::class, 'store'])->name('scenarios.store');
    Route::get('/scenarios/{scenario}', [ScenarioController::class, 'show'])->name('scenarios.show');
    Route::delete('/scenarios/{scenario}', [ScenarioController::class, 'destroy'])->name('scenarios.destroy');

    // Goals (OKR)
    Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
    Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
    Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::put('/webhooks/{webhook}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');
});
