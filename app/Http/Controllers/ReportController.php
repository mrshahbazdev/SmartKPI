<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Models\Problem;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $companies = Company::all(['id', 'name']);

        return Inertia::render('Reports/Index', [
            'companies' => $companies,
        ]);
    }

    public function generate(Request $request, RiskScoringService $riskService)
    {
        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'language' => ['required', 'in:de,en'],
            'period' => ['required', 'in:week,month,quarter'],
        ]);

        $lang = $validated['language'];
        $period = match ($validated['period']) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
        };

        $companies = $validated['company_id']
            ? Company::where('id', $validated['company_id'])->get()
            : Company::all();

        $reportData = [];

        foreach ($companies as $company) {
            $kpis = KpiDefinition::where('company_id', $company->id)
                ->where('is_template', false)
                ->where('is_active', true)
                ->with('latestValue')
                ->get();

            $kpiSummary = $kpis->map(function ($kpi) use ($lang, $period) {
                $latest = $kpi->latestValue;
                $values = KpiValue::where('kpi_definition_id', $kpi->id)
                    ->where('recorded_at', '>=', now()->subDays($period))
                    ->orderBy('recorded_at')
                    ->pluck('value')
                    ->map(fn ($v) => (float) $v);

                return [
                    'name' => $lang === 'de' ? $kpi->name_de : $kpi->name_en,
                    'current_value' => $latest ? (float) $latest->value : null,
                    'target' => $kpi->target_value ? (float) $kpi->target_value : null,
                    'status' => $latest?->status ?? 'no_data',
                    'unit' => $kpi->unit,
                    'trend_avg' => $values->count() > 0 ? round($values->avg(), 2) : null,
                    'trend_min' => $values->count() > 0 ? round($values->min(), 2) : null,
                    'trend_max' => $values->count() > 0 ? round($values->max(), 2) : null,
                ];
            });

            $deptIds = $company->departments()->pluck('id');
            $openProblems = Problem::whereIn('department_id', $deptIds)
                ->whereIn('status', ['open', 'investigating'])
                ->count();
            $resolvedProblems = Problem::whereIn('department_id', $deptIds)
                ->where('status', 'resolved')
                ->where('resolved_at', '>=', now()->subDays($period))
                ->count();

            $riskScore = $riskService->calculateRiskScore($company);

            $reportData[] = [
                'company' => $company->name,
                'risk_score' => (float) $riskScore->risk_score,
                'kpi_health' => (float) $riskScore->kpi_health,
                'open_problems' => $openProblems,
                'resolved_problems' => $resolvedProblems,
                'kpis' => $kpiSummary,
            ];
        }

        $title = $lang === 'de'
            ? match ($validated['period']) { 'week' => 'Wochenbericht', 'month' => 'Monatsbericht', 'quarter' => 'Quartalsbericht' }
            : match ($validated['period']) { 'week' => 'Weekly Report', 'month' => 'Monthly Report', 'quarter' => 'Quarterly Report' };

        return Inertia::render('Reports/Show', [
            'reportData' => $reportData,
            'title' => $title,
            'language' => $lang,
            'period' => $validated['period'],
            'generatedAt' => now()->toISOString(),
        ]);
    }
}
