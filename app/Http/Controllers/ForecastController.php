<?php

namespace App\Http\Controllers;

use App\Models\Forecast;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Services\ForecastService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForecastController extends Controller
{
    public function index(Request $request)
    {
        $kpis = KpiDefinition::where('is_template', false)
            ->where('is_active', true)
            ->with('department', 'company')
            ->get()
            ->map(fn ($kpi) => [
                'id' => $kpi->id,
                'name_de' => $kpi->name_de,
                'name_en' => $kpi->name_en,
                'department' => $kpi->department?->name,
                'company' => $kpi->company?->name,
                'unit' => $kpi->unit,
            ]);

        $selectedKpi = $request->kpi_id ? KpiDefinition::find($request->kpi_id) : null;
        $forecasts = [];
        $historicalValues = [];

        if ($selectedKpi) {
            $horizon = $request->horizon ?? '30d';
            $forecasts = Forecast::where('kpi_definition_id', $selectedKpi->id)
                ->where('horizon', $horizon)
                ->orderBy('forecast_date')
                ->get();

            $historicalValues = KpiValue::where('kpi_definition_id', $selectedKpi->id)
                ->orderBy('recorded_at')
                ->limit(90)
                ->get()
                ->map(fn ($v) => [
                    'date' => $v->recorded_at->toDateString(),
                    'value' => (float) $v->value,
                    'status' => $v->status,
                ]);
        }

        return Inertia::render('Forecast/Index', [
            'kpis' => $kpis,
            'selectedKpiId' => $request->kpi_id,
            'horizon' => $request->horizon ?? '30d',
            'forecasts' => $forecasts,
            'historicalValues' => $historicalValues,
        ]);
    }

    public function generate(Request $request, ForecastService $service)
    {
        $validated = $request->validate([
            'kpi_id' => ['required', 'exists:kpi_definitions,id'],
            'horizon' => ['required', 'in:7d,30d,90d'],
        ]);

        $kpi = KpiDefinition::findOrFail($validated['kpi_id']);
        $service->generateForecast($kpi, $validated['horizon']);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function earlyWarnings(ForecastService $service)
    {
        $kpis = KpiDefinition::where('is_template', false)
            ->where('is_active', true)
            ->whereNotNull('critical_threshold')
            ->with('department', 'company')
            ->get();

        $warnings = [];
        foreach ($kpis as $kpi) {
            $warning = $service->earlyWarning($kpi);
            if ($warning) {
                $warning['department'] = $kpi->department?->name;
                $warning['company'] = $kpi->company?->name;
                $warning['kpi_id'] = $kpi->id;
                $warnings[] = $warning;
            }
        }

        // Sort by urgency
        usort($warnings, fn ($a, $b) => $a['days_until_breach'] <=> $b['days_until_breach']);

        return Inertia::render('Forecast/EarlyWarnings', [
            'warnings' => $warnings,
        ]);
    }
}
