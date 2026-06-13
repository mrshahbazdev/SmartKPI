<?php

namespace App\Http\Controllers;

use App\Http\Resources\KpiDefinitionResource;
use App\Models\Department;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentDashboardController extends Controller
{
    public function show(Request $request, Department $department)
    {
        $days = (int) ($request->input('days', 30));
        $since = Carbon::now()->subDays($days);

        $department->load('company');

        $kpis = KpiDefinition::where('department_id', $department->id)
            ->where('is_template', false)
            ->with(['latestValue', 'values' => function ($q) use ($since) {
                $q->where('recorded_at', '>=', $since)->orderBy('recorded_at');
            }])
            ->get();

        $kpiData = $kpis->map(function ($kpi) use ($days) {
            $latest = $kpi->latestValue;
            $values = $kpi->values;
            $trend = $this->calculateTrend($values);

            return [
                'id' => $kpi->id,
                'name_de' => $kpi->name_de,
                'name_en' => $kpi->name_en,
                'unit' => $kpi->unit,
                'target_value' => $kpi->target_value ? (float) $kpi->target_value : null,
                'direction' => $kpi->direction,
                'category' => $kpi->category,
                'current_value' => $latest ? (float) $latest->value : null,
                'status' => $latest->status ?? 'on_target',
                'trend_percent' => $trend,
                'sparkline' => $values->map(fn ($v) => (float) $v->value)->values(),
                'values' => $values->map(fn ($v) => [
                    'value' => (float) $v->value,
                    'date' => $v->recorded_at->format('Y-m-d'),
                    'status' => $v->status,
                ])->values(),
            ];
        });

        $statusCounts = [
            'on_target' => $kpiData->where('status', 'on_target')->count(),
            'warning' => $kpiData->where('status', 'warning')->count(),
            'critical' => $kpiData->where('status', 'critical')->count(),
        ];

        return Inertia::render('Dashboard/Department', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'company' => $department->company ? [
                    'id' => $department->company->id,
                    'name' => $department->company->name,
                ] : null,
            ],
            'kpis' => $kpiData,
            'statusCounts' => $statusCounts,
            'days' => $days,
        ]);
    }

    private function calculateTrend($values): ?float
    {
        if ($values->count() < 2) return null;

        $recent = $values->last();
        $previous = $values->reverse()->skip(1)->first();

        if (!$previous || (float) $previous->value == 0) return null;

        return round(((float) $recent->value - (float) $previous->value) / (float) $previous->value * 100, 1);
    }
}
