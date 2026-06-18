<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\KpiDefinition;
use App\Models\KpiRelationship;
use App\Models\KpiValue;
use App\Models\Scenario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ScenarioController extends Controller
{
    public function index()
    {
        $scenarios = Scenario::with(['company', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Scenarios/Index', [
            'scenarios' => $scenarios,
        ]);
    }

    public function create()
    {
        $companies = Company::all(['id', 'name']);
        $kpis = KpiDefinition::where('is_template', false)
            ->where('is_active', true)
            ->get(['id', 'name_de', 'name_en', 'company_id', 'unit', 'target_value']);

        return Inertia::render('Scenarios/Create', [
            'companies' => $companies,
            'kpis' => $kpis,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assumptions' => ['required', 'array', 'min:1'],
            'assumptions.*.kpi_id' => ['required', 'exists:kpi_definitions,id'],
            'assumptions.*.change_pct' => ['required', 'numeric'],
        ]);

        // Project outcomes based on assumptions + KPI relationships
        $outcomes = $this->projectOutcomes($validated['assumptions']);

        $scenario = Scenario::create([
            'company_id' => $validated['company_id'],
            'created_by' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assumptions' => $validated['assumptions'],
            'projected_outcomes' => $outcomes,
        ]);

        return redirect()->route('scenarios.show', $scenario)->with('success', __('common.success'));
    }

    public function show(Scenario $scenario)
    {
        $scenario->load(['company', 'creator']);

        // Enrich assumptions with KPI names
        $enrichedAssumptions = collect($scenario->assumptions)->map(function ($a) {
            $kpi = KpiDefinition::find($a['kpi_id']);
            return array_merge($a, [
                'kpi_name_de' => $kpi?->name_de,
                'kpi_name_en' => $kpi?->name_en,
                'unit' => $kpi?->unit,
                'current_value' => $kpi ? (float) (KpiValue::where('kpi_definition_id', $kpi->id)->orderByDesc('recorded_at')->first()?->value ?? 0) : 0,
            ]);
        });

        return Inertia::render('Scenarios/Show', [
            'scenario' => $scenario,
            'enrichedAssumptions' => $enrichedAssumptions,
        ]);
    }

    public function destroy(Scenario $scenario)
    {
        $scenario->delete();
        return redirect()->route('scenarios.index')->with('success', __('common.success'));
    }

    private function projectOutcomes(array $assumptions): array
    {
        $outcomes = [];

        foreach ($assumptions as $assumption) {
            $kpiId = $assumption['kpi_id'];
            $changePct = $assumption['change_pct'];

            // Find downstream effects via KPI relationships
            $effects = KpiRelationship::where('cause_kpi_id', $kpiId)
                ->with('effectKpi')
                ->get();

            foreach ($effects as $effect) {
                $effectKpi = $effect->effectKpi;
                if (!$effectKpi) continue;

                $latestValue = KpiValue::where('kpi_definition_id', $effectKpi->id)
                    ->orderByDesc('recorded_at')
                    ->first();

                $currentVal = $latestValue ? (float) $latestValue->value : 0;
                $projectedChange = $changePct * (float) $effect->weight;
                $projectedValue = $currentVal * (1 + $projectedChange / 100);

                $outcomes[] = [
                    'kpi_id' => $effectKpi->id,
                    'kpi_name_de' => $effectKpi->name_de,
                    'kpi_name_en' => $effectKpi->name_en,
                    'current_value' => $currentVal,
                    'projected_value' => round($projectedValue, 2),
                    'change_pct' => round($projectedChange, 2),
                    'unit' => $effectKpi->unit,
                    'caused_by_kpi_id' => $kpiId,
                    'weight' => (float) $effect->weight,
                ];
            }
        }

        return $outcomes;
    }
}
