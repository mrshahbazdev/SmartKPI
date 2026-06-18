<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Company;
use App\Models\CompanyRiskScore;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Models\Problem;
use Illuminate\Support\Collection;

class RiskScoringService
{
    /**
     * Calculate risk score for a company (0-100, higher = more risk).
     */
    public function calculateRiskScore(Company $company): CompanyRiskScore
    {
        $today = now()->toDateString();

        $existing = CompanyRiskScore::where('company_id', $company->id)
            ->where('score_date', $today)
            ->first();

        if ($existing) {
            return $existing;
        }

        $deptIds = $company->departments()->pluck('id');
        $kpis = KpiDefinition::where('company_id', $company->id)
            ->where('is_template', false)
            ->where('is_active', true)
            ->get();

        // KPI health: % of KPIs on target
        $onTarget = 0;
        $totalWithValues = 0;
        foreach ($kpis as $kpi) {
            $latest = KpiValue::where('kpi_definition_id', $kpi->id)
                ->orderByDesc('recorded_at')
                ->first();
            if ($latest) {
                $totalWithValues++;
                if ($latest->status === 'on_target') {
                    $onTarget++;
                }
            }
        }
        $kpiHealth = $totalWithValues > 0 ? round(($onTarget / $totalWithValues) * 100, 2) : 100;

        // Problem counts
        $openProblems = Problem::whereIn('department_id', $deptIds)
            ->whereIn('status', ['open', 'investigating'])
            ->count();
        $criticalProblems = Problem::whereIn('department_id', $deptIds)
            ->whereIn('status', ['open', 'investigating'])
            ->where('severity', 'critical')
            ->count();

        // Overdue actions
        $overdueActions = Action::where('status', 'open')
            ->where('deadline', '<', now())
            ->whereHas('problem', fn ($q) => $q->whereIn('department_id', $deptIds))
            ->count();

        // Weighted risk score
        $kpiRisk = (100 - $kpiHealth) * 0.4;
        $problemRisk = min($openProblems * 5, 30) * 0.3;
        $criticalRisk = $criticalProblems * 10;
        $overdueRisk = min($overdueActions * 8, 20) * 0.3;

        $riskScore = min(round($kpiRisk + $problemRisk + $criticalRisk + $overdueRisk, 2), 100);

        return CompanyRiskScore::create([
            'company_id' => $company->id,
            'score_date' => $today,
            'risk_score' => $riskScore,
            'kpi_health' => $kpiHealth,
            'open_problems' => $openProblems,
            'critical_problems' => $criticalProblems,
            'overdue_actions' => $overdueActions,
            'breakdown' => [
                'kpi_risk' => round($kpiRisk, 2),
                'problem_risk' => round($problemRisk, 2),
                'critical_risk' => round($criticalRisk, 2),
                'overdue_risk' => round($overdueRisk, 2),
            ],
        ]);
    }

    /**
     * Calculate risk scores for all companies in the holding.
     */
    public function calculateHoldingRisk(): Collection
    {
        $companies = Company::all();
        return $companies->map(fn ($c) => $this->calculateRiskScore($c));
    }
}
