<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\KpiDefinition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyDashboardController extends Controller
{
    public function show(Request $request, Company $company)
    {
        $days = (int) ($request->input('days', 30));
        $since = Carbon::now()->subDays($days);

        $company->load('departments');

        $departmentSummaries = $company->departments->map(function ($dept) use ($since) {
            $kpis = KpiDefinition::where('department_id', $dept->id)
                ->where('is_template', false)
                ->with('latestValue')
                ->get();

            $statuses = ['on_target' => 0, 'warning' => 0, 'critical' => 0];
            foreach ($kpis as $kpi) {
                $status = $kpi->latestValue?->status ?? 'on_target';
                $statuses[$status]++;
            }

            $riskScore = $kpis->count() > 0
                ? round(($statuses['warning'] * 50 + $statuses['critical'] * 100) / $kpis->count(), 0)
                : 0;

            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'kpi_count' => $kpis->count(),
                'statuses' => $statuses,
                'risk_score' => $riskScore,
            ];
        });

        $topRisks = $departmentSummaries->sortByDesc('risk_score')->take(5)->values();
        $topWins = $departmentSummaries->sortBy('risk_score')
            ->filter(fn ($d) => $d['kpi_count'] > 0)
            ->take(5)->values();

        $companyKpis = KpiDefinition::where('company_id', $company->id)
            ->whereNull('department_id')
            ->where('is_template', false)
            ->with('latestValue')
            ->get()
            ->map(function ($kpi) {
                return [
                    'id' => $kpi->id,
                    'name_de' => $kpi->name_de,
                    'name_en' => $kpi->name_en,
                    'unit' => $kpi->unit,
                    'current_value' => $kpi->latestValue ? (float) $kpi->latestValue->value : null,
                    'status' => $kpi->latestValue?->status ?? 'on_target',
                    'target_value' => $kpi->target_value ? (float) $kpi->target_value : null,
                ];
            });

        return Inertia::render('Dashboard/Company', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'industry' => $company->industry,
            ],
            'departments' => $departmentSummaries,
            'topRisks' => $topRisks,
            'topWins' => $topWins,
            'companyKpis' => $companyKpis,
            'days' => $days,
        ]);
    }
}
