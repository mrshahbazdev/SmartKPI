<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CrossCompanyEffect;
use App\Models\KpiDefinition;
use App\Models\KpiRelationship;
use App\Models\Problem;

class CrossCompanyService
{
    /**
     * Detect cross-company effects from a problem.
     * Walks KPI relationships to find effects that span companies.
     */
    public function detectCrossCompanyEffects(Problem $problem): array
    {
        $kpi = $problem->kpiDefinition;
        if (!$kpi) {
            return [];
        }

        $sourceCompanyId = $kpi->company_id;
        $effects = [];

        // Find KPI relationships where this KPI is a cause
        $relationships = KpiRelationship::where('cause_kpi_id', $kpi->id)
            ->with('effectKpi.company')
            ->get();

        foreach ($relationships as $rel) {
            $effectKpi = $rel->effectKpi;
            if (!$effectKpi || $effectKpi->company_id === $sourceCompanyId) {
                continue;
            }

            // Cross-company effect detected
            $existing = CrossCompanyEffect::where('source_problem_id', $problem->id)
                ->where('affected_company_id', $effectKpi->company_id)
                ->where('affected_kpi_id', $effectKpi->id)
                ->first();

            if (!$existing) {
                $effect = CrossCompanyEffect::create([
                    'source_problem_id' => $problem->id,
                    'source_company_id' => $sourceCompanyId,
                    'affected_company_id' => $effectKpi->company_id,
                    'affected_kpi_id' => $effectKpi->id,
                    'impact_type' => $this->inferImpactType($kpi, $effectKpi),
                    'impact_score' => round($rel->weight * $rel->confidence * 100, 2),
                    'description' => sprintf(
                        '%s → %s (Gewicht: %s)',
                        $kpi->name_de,
                        $effectKpi->name_de,
                        $rel->weight
                    ),
                    'status' => 'detected',
                ]);
                $effects[] = $effect;
            }
        }

        // Also walk the effect chain recursively (depth 1 for cross-company)
        foreach ($relationships as $rel) {
            $secondLevel = KpiRelationship::where('cause_kpi_id', $rel->effect_kpi_id)
                ->with('effectKpi.company')
                ->get();

            foreach ($secondLevel as $rel2) {
                $effectKpi2 = $rel2->effectKpi;
                if (!$effectKpi2 || $effectKpi2->company_id === $sourceCompanyId) {
                    continue;
                }

                $existing = CrossCompanyEffect::where('source_problem_id', $problem->id)
                    ->where('affected_company_id', $effectKpi2->company_id)
                    ->where('affected_kpi_id', $effectKpi2->id)
                    ->first();

                if (!$existing) {
                    $combinedWeight = $rel->weight * $rel2->weight;
                    $effect = CrossCompanyEffect::create([
                        'source_problem_id' => $problem->id,
                        'source_company_id' => $sourceCompanyId,
                        'affected_company_id' => $effectKpi2->company_id,
                        'affected_kpi_id' => $effectKpi2->id,
                        'impact_type' => $this->inferImpactType($kpi, $effectKpi2),
                        'impact_score' => round($combinedWeight * 100, 2),
                        'description' => sprintf(
                            '%s → %s → %s',
                            $kpi->name_de,
                            $rel->effectKpi->name_de,
                            $effectKpi2->name_de
                        ),
                        'status' => 'detected',
                    ]);
                    $effects[] = $effect;
                }
            }
        }

        return $effects;
    }

    /**
     * Get all cross-company benchmarking data for a KPI category.
     */
    public function benchmark(string $category): array
    {
        $companies = Company::all();
        $benchmarks = [];

        foreach ($companies as $company) {
            $kpis = KpiDefinition::where('company_id', $company->id)
                ->where('category', $category)
                ->where('is_template', false)
                ->where('is_active', true)
                ->with(['latestValue'])
                ->get();

            foreach ($kpis as $kpi) {
                $latest = $kpi->latestValue;
                if (!$latest) continue;

                $benchmarks[] = [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'kpi_id' => $kpi->id,
                    'kpi_name_de' => $kpi->name_de,
                    'kpi_name_en' => $kpi->name_en,
                    'value' => (float) $latest->value,
                    'target' => $kpi->target_value ? (float) $kpi->target_value : null,
                    'status' => $latest->status,
                    'unit' => $kpi->unit,
                ];
            }
        }

        return $benchmarks;
    }

    private function inferImpactType(KpiDefinition $cause, KpiDefinition $effect): string
    {
        $causeCategory = strtolower($cause->category ?? '');
        $effectCategory = strtolower($effect->category ?? '');

        if (str_contains($causeCategory, 'supply') || str_contains($effectCategory, 'supply') ||
            str_contains($causeCategory, 'lieferkette') || str_contains($effectCategory, 'lieferkette')) {
            return 'supply_chain';
        }
        if (str_contains($causeCategory, 'customer') || str_contains($effectCategory, 'customer') ||
            str_contains($causeCategory, 'kunden') || str_contains($effectCategory, 'kunden')) {
            return 'customer_base';
        }
        if (str_contains($causeCategory, 'financ') || str_contains($effectCategory, 'financ') ||
            str_contains($causeCategory, 'finanz') || str_contains($effectCategory, 'finanz')) {
            return 'financial';
        }

        return 'shared_resource';
    }
}
