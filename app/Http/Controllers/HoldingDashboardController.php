<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\KpiDefinition;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HoldingDashboardController extends Controller
{
    public function show(Request $request, Tenant $tenant)
    {
        $days = (int) ($request->input('days', 30));

        $companies = Company::where('tenant_id', $tenant->id)
            ->with('departments')
            ->get();

        $companySummaries = $companies->map(function ($company) {
            $totalKpis = 0;
            $statuses = ['on_target' => 0, 'warning' => 0, 'critical' => 0];

            foreach ($company->departments as $dept) {
                $kpis = KpiDefinition::where('department_id', $dept->id)
                    ->where('is_template', false)
                    ->with('latestValue')
                    ->get();

                foreach ($kpis as $kpi) {
                    $totalKpis++;
                    $status = $kpi->latestValue?->status ?? 'on_target';
                    $statuses[$status]++;
                }
            }

            $riskScore = $totalKpis > 0
                ? round(($statuses['warning'] * 50 + $statuses['critical'] * 100) / $totalKpis, 0)
                : 0;

            return [
                'id' => $company->id,
                'name' => $company->name,
                'industry' => $company->industry,
                'department_count' => $company->departments->count(),
                'kpi_count' => $totalKpis,
                'statuses' => $statuses,
                'risk_score' => $riskScore,
            ];
        });

        return Inertia::render('Dashboard/Holding', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
            'companies' => $companySummaries,
            'days' => $days,
        ]);
    }
}
