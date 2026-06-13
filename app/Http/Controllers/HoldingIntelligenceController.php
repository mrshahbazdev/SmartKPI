<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyRiskScore;
use App\Models\CrossCompanyEffect;
use App\Models\KpiDefinition;
use App\Services\CrossCompanyService;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HoldingIntelligenceController extends Controller
{
    public function riskOverview(RiskScoringService $riskService)
    {
        $scores = $riskService->calculateHoldingRisk();
        $companies = Company::all(['id', 'name']);

        // Historical scores for chart (last 30 days)
        $history = CompanyRiskScore::orderBy('score_date')
            ->where('score_date', '>=', now()->subDays(30))
            ->get()
            ->groupBy('company_id');

        return Inertia::render('HoldingIntelligence/RiskOverview', [
            'scores' => $scores,
            'companies' => $companies,
            'history' => $history,
        ]);
    }

    public function crossCompanyEffects()
    {
        $effects = CrossCompanyEffect::with(['sourceProblem', 'sourceCompany', 'affectedCompany', 'affectedKpi'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $companies = Company::all(['id', 'name']);

        return Inertia::render('HoldingIntelligence/CrossCompanyEffects', [
            'effects' => $effects,
            'companies' => $companies,
        ]);
    }

    public function benchmark(Request $request, CrossCompanyService $service)
    {
        $categories = KpiDefinition::where('is_template', false)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $category = $request->category ?? $categories->first();
        $benchmarks = $category ? $service->benchmark($category) : [];
        $companies = Company::all(['id', 'name']);

        return Inertia::render('HoldingIntelligence/Benchmark', [
            'categories' => $categories,
            'benchmarks' => $benchmarks,
            'companies' => $companies,
            'selectedCategory' => $category,
        ]);
    }
}
