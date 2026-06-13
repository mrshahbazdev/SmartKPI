<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Services\ProblemDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiKpiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KpiDefinition::where('is_template', false)->with('department', 'company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function show(KpiDefinition $kpi): JsonResponse
    {
        $kpi->load(['department', 'company', 'responsibleUsers', 'latestValue']);
        return response()->json($kpi);
    }

    public function values(KpiDefinition $kpi, Request $request): JsonResponse
    {
        $query = KpiValue::where('kpi_definition_id', $kpi->id)->orderByDesc('recorded_at');

        if ($request->filled('from')) {
            $query->where('recorded_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('recorded_at', '<=', $request->to);
        }

        return response()->json($query->paginate($request->per_page ?? 50));
    }

    public function storeValue(Request $request, KpiDefinition $kpi): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'recorded_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $status = $this->calculateStatus($kpi, (float) $validated['value']);

        $kpiValue = KpiValue::create([
            'kpi_definition_id' => $kpi->id,
            'value' => $validated['value'],
            'recorded_at' => $validated['recorded_at'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => Auth::id(),
            'status' => $status,
        ]);

        app(ProblemDetectionService::class)->checkKpi($kpi, $kpiValue);

        return response()->json($kpiValue, 201);
    }

    public function analytics(KpiDefinition $kpi): JsonResponse
    {
        $values = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderBy('recorded_at')
            ->pluck('value', 'recorded_at')
            ->map(fn ($v) => (float) $v);

        if ($values->isEmpty()) {
            return response()->json(['error' => 'No data'], 404);
        }

        $numericValues = $values->values();

        return response()->json([
            'count' => $numericValues->count(),
            'min' => $numericValues->min(),
            'max' => $numericValues->max(),
            'avg' => round($numericValues->avg(), 4),
            'median' => round($this->median($numericValues->toArray()), 4),
            'std_dev' => round($this->stdDev($numericValues), 4),
            'latest' => $numericValues->last(),
            'target' => $kpi->target_value ? (float) $kpi->target_value : null,
            'direction' => $kpi->direction,
            'trend' => $this->calculateTrend($numericValues),
        ]);
    }

    private function calculateStatus(KpiDefinition $kpi, float $value): string
    {
        if (!$kpi->target_value) return 'on_target';
        if ($kpi->direction === 'higher_better') {
            if ($kpi->critical_threshold && $value <= $kpi->critical_threshold) return 'critical';
            if ($kpi->warning_threshold && $value <= $kpi->warning_threshold) return 'warning';
            return 'on_target';
        }
        if ($kpi->critical_threshold && $value >= $kpi->critical_threshold) return 'critical';
        if ($kpi->warning_threshold && $value >= $kpi->warning_threshold) return 'warning';
        return 'on_target';
    }

    private function calculateTrend($values): string
    {
        if ($values->count() < 3) return 'stable';
        $last3 = $values->slice(-3)->values();
        if ($last3[2] > $last3[1] && $last3[1] > $last3[0]) return 'up';
        if ($last3[2] < $last3[1] && $last3[1] < $last3[0]) return 'down';
        return 'stable';
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $mid = (int) floor($count / 2);
        return ($count % 2 === 0) ? ($values[$mid - 1] + $values[$mid]) / 2 : $values[$mid];
    }

    private function stdDev($values): float
    {
        $mean = $values->avg();
        $sumSqDiff = $values->reduce(fn ($c, $v) => $c + pow($v - $mean, 2), 0);
        return sqrt($sumSqDiff / max($values->count(), 1));
    }
}
