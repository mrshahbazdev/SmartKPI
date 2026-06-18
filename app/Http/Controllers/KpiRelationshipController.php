<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\KpiRelationship;
use App\Models\KpiValue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KpiRelationshipController extends Controller
{
    public function index(Request $request)
    {
        $relationships = KpiRelationship::with(['causeKpi', 'effectKpi'])
            ->orderByDesc('created_at')
            ->get();

        $kpis = KpiDefinition::where('is_template', false)
            ->where('is_active', true)
            ->with('department')
            ->get()
            ->map(fn ($kpi) => [
                'id' => $kpi->id,
                'name_de' => $kpi->name_de,
                'name_en' => $kpi->name_en,
                'department' => $kpi->department?->name,
                'category' => $kpi->category,
            ]);

        // Build graph data for visualization
        $nodes = $kpis->map(fn ($kpi) => [
            'id' => $kpi['id'],
            'label_de' => $kpi['name_de'],
            'label_en' => $kpi['name_en'],
            'department' => $kpi['department'],
            'category' => $kpi['category'],
        ]);

        $edges = $relationships->map(fn ($rel) => [
            'id' => $rel->id,
            'source' => $rel->cause_kpi_id,
            'target' => $rel->effect_kpi_id,
            'weight' => (float) $rel->weight,
            'confidence' => (float) $rel->confidence,
            'lag_days' => $rel->lag_days,
            'description' => $rel->description,
        ]);

        return Inertia::render('Relationships/Index', [
            'relationships' => $relationships,
            'kpis' => $kpis,
            'graph' => [
                'nodes' => $nodes->values(),
                'edges' => $edges->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cause_kpi_id' => ['required', 'exists:kpi_definitions,id'],
            'effect_kpi_id' => ['required', 'exists:kpi_definitions,id', 'different:cause_kpi_id'],
            'weight' => ['required', 'numeric', 'between:0,1'],
            'confidence' => ['nullable', 'numeric', 'between:0,1'],
            'lag_days' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        KpiRelationship::create($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function update(Request $request, KpiRelationship $relationship)
    {
        $validated = $request->validate([
            'weight' => ['sometimes', 'numeric', 'between:0,1'],
            'confidence' => ['nullable', 'numeric', 'between:0,1'],
            'lag_days' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $relationship->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroy(KpiRelationship $relationship)
    {
        $relationship->delete();
        return redirect()->back()->with('success', __('common.success'));
    }

    /**
     * Root cause tracing: walk the cause chain from a given KPI.
     */
    public function trace(KpiDefinition $kpi)
    {
        $chain = $this->walkCauseChain($kpi->id, [], 0);

        return Inertia::render('Relationships/Trace', [
            'kpi' => [
                'id' => $kpi->id,
                'name_de' => $kpi->name_de,
                'name_en' => $kpi->name_en,
                'department' => $kpi->department?->name,
                'company' => $kpi->company?->name,
            ],
            'chain' => $chain,
        ]);
    }

    /**
     * Correlation analysis between two KPIs.
     */
    public function correlate(Request $request)
    {
        $validated = $request->validate([
            'kpi_a_id' => ['required', 'exists:kpi_definitions,id'],
            'kpi_b_id' => ['required', 'exists:kpi_definitions,id'],
        ]);

        $valuesA = KpiValue::where('kpi_definition_id', $validated['kpi_a_id'])
            ->orderBy('recorded_at')
            ->pluck('value', 'recorded_at')
            ->map(fn ($v) => (float) $v);

        $valuesB = KpiValue::where('kpi_definition_id', $validated['kpi_b_id'])
            ->orderBy('recorded_at')
            ->pluck('value', 'recorded_at')
            ->map(fn ($v) => (float) $v);

        // Align on common dates
        $commonDates = $valuesA->keys()->intersect($valuesB->keys());
        $a = $commonDates->map(fn ($d) => $valuesA[$d])->values();
        $b = $commonDates->map(fn ($d) => $valuesB[$d])->values();

        $correlation = $this->pearsonCorrelation($a, $b);

        // Lag detection: check correlations at different lags
        $lagCorrelations = [];
        for ($lag = 1; $lag <= 7; $lag++) {
            $shiftedB = KpiValue::where('kpi_definition_id', $validated['kpi_b_id'])
                ->orderBy('recorded_at')
                ->get()
                ->map(fn ($v) => ['date' => $v->recorded_at->subDays($lag)->format('Y-m-d'), 'value' => (float) $v->value]);

            $shiftedMap = $shiftedB->pluck('value', 'date');
            $commonShifted = $valuesA->keys()->intersect($shiftedMap->keys());
            $aLag = $commonShifted->map(fn ($d) => $valuesA[$d])->values();
            $bLag = $commonShifted->map(fn ($d) => $shiftedMap[$d])->values();

            $lagCorrelations[$lag] = $this->pearsonCorrelation($aLag, $bLag);
        }

        return response()->json([
            'correlation' => $correlation,
            'lag_correlations' => $lagCorrelations,
            'common_points' => $commonDates->count(),
        ]);
    }

    private function walkCauseChain(int $kpiId, array $visited, int $depth): array
    {
        if (in_array($kpiId, $visited) || $depth > 10) {
            return [];
        }

        $visited[] = $kpiId;
        $causes = KpiRelationship::where('effect_kpi_id', $kpiId)
            ->with('causeKpi.department.company')
            ->get();

        $chain = [];
        foreach ($causes as $rel) {
            $node = [
                'kpi_id' => $rel->cause_kpi_id,
                'name_de' => $rel->causeKpi->name_de,
                'name_en' => $rel->causeKpi->name_en,
                'department' => $rel->causeKpi->department?->name,
                'company' => $rel->causeKpi->department?->company?->name,
                'weight' => (float) $rel->weight,
                'confidence' => (float) $rel->confidence,
                'lag_days' => $rel->lag_days,
                'depth' => $depth,
                'causes' => $this->walkCauseChain($rel->cause_kpi_id, $visited, $depth + 1),
            ];
            $chain[] = $node;
        }

        return $chain;
    }

    private function pearsonCorrelation($a, $b): ?float
    {
        $n = min($a->count(), $b->count());
        if ($n < 3) {
            return null;
        }

        $meanA = $a->avg();
        $meanB = $b->avg();

        $numerator = 0;
        $denomA = 0;
        $denomB = 0;

        for ($i = 0; $i < $n; $i++) {
            $da = $a[$i] - $meanA;
            $db = $b[$i] - $meanB;
            $numerator += $da * $db;
            $denomA += $da * $da;
            $denomB += $db * $db;
        }

        $denom = sqrt($denomA * $denomB);
        if ($denom == 0) {
            return null;
        }

        return round($numerator / $denom, 4);
    }
}
