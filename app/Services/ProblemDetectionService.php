<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Models\Problem;
use App\Notifications\KpiAlertNotification;
use Illuminate\Support\Collection;

class ProblemDetectionService
{
    /**
     * Run all detection checks for a KPI after a new value is recorded.
     */
    public function checkKpi(KpiDefinition $kpi, KpiValue $latestValue): array
    {
        $problems = [];

        // 1. Threshold-based detection (built-in)
        if ($latestValue->status === 'critical' || $latestValue->status === 'warning') {
            $problem = $this->detectThresholdBreach($kpi, $latestValue);
            if ($problem) {
                $problems[] = $problem;
            }
        }

        // 2. Trend detection (3 consecutive drops/rises in wrong direction)
        $trendProblem = $this->detectTrend($kpi);
        if ($trendProblem) {
            $problems[] = $trendProblem;
        }

        // 3. Anomaly detection (z-score)
        $anomalyProblem = $this->detectAnomaly($kpi, $latestValue);
        if ($anomalyProblem) {
            $problems[] = $anomalyProblem;
        }

        // 4. Company-specific alert rules
        $ruleProblem = $this->checkAlertRules($kpi, $latestValue);
        $problems = array_merge($problems, $ruleProblem);

        // Notify responsible users + detect cross-company effects
        $crossCompanyService = app(CrossCompanyService::class);
        foreach ($problems as $problem) {
            $this->notifyResponsibleUsers($kpi, $problem);
            $crossCompanyService->detectCrossCompanyEffects($problem);
        }

        return $problems;
    }

    private function detectThresholdBreach(KpiDefinition $kpi, KpiValue $value): ?Problem
    {
        $existingOpen = Problem::where('kpi_definition_id', $kpi->id)
            ->where('status', 'open')
            ->where('severity', $this->mapStatusToSeverity($value->status))
            ->first();

        if ($existingOpen) {
            return null;
        }

        $severity = $this->mapStatusToSeverity($value->status);
        $locale = 'de';

        return Problem::create([
            'kpi_definition_id' => $kpi->id,
            'department_id' => $kpi->department_id,
            'title' => $kpi->name_de . ' — Schwellenwert überschritten',
            'description' => sprintf(
                '%s hat den Wert %s erreicht (Ziel: %s)',
                $kpi->name_de,
                number_format((float) $value->value, 2, ',', '.'),
                number_format((float) $kpi->target_value, 2, ',', '.')
            ),
            'severity' => $severity,
            'status' => 'open',
            'detected_at' => now(),
        ]);
    }

    private function detectTrend(KpiDefinition $kpi): ?Problem
    {
        $recentValues = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderByDesc('recorded_at')
            ->limit(4)
            ->pluck('value')
            ->reverse()
            ->values();

        if ($recentValues->count() < 4) {
            return null;
        }

        $consecutiveDeclines = 0;
        $consecutiveIncreases = 0;

        for ($i = 1; $i < $recentValues->count(); $i++) {
            if ($recentValues[$i] < $recentValues[$i - 1]) {
                $consecutiveDeclines++;
                $consecutiveIncreases = 0;
            } elseif ($recentValues[$i] > $recentValues[$i - 1]) {
                $consecutiveIncreases++;
                $consecutiveDeclines = 0;
            }
        }

        $isBadTrend = false;
        if ($kpi->direction === 'higher_better' && $consecutiveDeclines >= 3) {
            $isBadTrend = true;
        }
        if ($kpi->direction === 'lower_better' && $consecutiveIncreases >= 3) {
            $isBadTrend = true;
        }

        if (!$isBadTrend) {
            return null;
        }

        $existingTrend = Problem::where('kpi_definition_id', $kpi->id)
            ->where('status', 'open')
            ->where('title', 'like', '%Negativtrend%')
            ->first();

        if ($existingTrend) {
            return null;
        }

        return Problem::create([
            'kpi_definition_id' => $kpi->id,
            'department_id' => $kpi->department_id,
            'title' => $kpi->name_de . ' — Negativtrend erkannt',
            'description' => sprintf(
                '%s zeigt 3 aufeinanderfolgende Verschlechterungen',
                $kpi->name_de
            ),
            'severity' => 'medium',
            'status' => 'open',
            'detected_at' => now(),
        ]);
    }

    private function detectAnomaly(KpiDefinition $kpi, KpiValue $latestValue): ?Problem
    {
        $values = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderByDesc('recorded_at')
            ->limit(30)
            ->pluck('value')
            ->map(fn ($v) => (float) $v);

        if ($values->count() < 10) {
            return null;
        }

        $mean = $values->avg();
        $stdDev = $this->stdDev($values);

        if ($stdDev == 0) {
            return null;
        }

        $zScore = abs(((float) $latestValue->value - $mean) / $stdDev);

        if ($zScore < 2.5) {
            return null;
        }

        $existingAnomaly = Problem::where('kpi_definition_id', $kpi->id)
            ->where('status', 'open')
            ->where('title', 'like', '%Anomalie%')
            ->first();

        if ($existingAnomaly) {
            return null;
        }

        return Problem::create([
            'kpi_definition_id' => $kpi->id,
            'department_id' => $kpi->department_id,
            'title' => $kpi->name_de . ' — Anomalie erkannt',
            'description' => sprintf(
                'Statistisch auffälliger Wert: %s (Z-Score: %s)',
                number_format((float) $latestValue->value, 2, ',', '.'),
                number_format($zScore, 1)
            ),
            'severity' => $zScore >= 3.0 ? 'high' : 'medium',
            'status' => 'open',
            'detected_at' => now(),
        ]);
    }

    private function checkAlertRules(KpiDefinition $kpi, KpiValue $value): array
    {
        $problems = [];

        $rules = AlertRule::where('is_active', true)
            ->where(function ($q) use ($kpi) {
                $q->where('kpi_definition_id', $kpi->id)
                    ->orWhereNull('kpi_definition_id');
            })
            ->where('company_id', $kpi->company_id)
            ->get();

        foreach ($rules as $rule) {
            if ($this->evaluateRule($rule, $kpi, $value) && $rule->auto_create_problem) {
                $problem = Problem::create([
                    'kpi_definition_id' => $kpi->id,
                    'department_id' => $kpi->department_id,
                    'title' => $rule->name . ' — ' . $kpi->name_de,
                    'description' => 'Automatisch erkannt durch Regel: ' . $rule->name,
                    'severity' => $rule->severity,
                    'status' => 'open',
                    'assigned_to' => $rule->notify_user_id,
                    'detected_at' => now(),
                ]);

                if ($rule->notify_user_id && $rule->notifyUser) {
                    $rule->notifyUser->notify(new KpiAlertNotification($problem, $kpi));
                }

                $problems[] = $problem;
            }
        }

        return $problems;
    }

    private function evaluateRule(AlertRule $rule, KpiDefinition $kpi, KpiValue $value): bool
    {
        $conditions = $rule->conditions;
        $val = (float) $value->value;

        if ($rule->type === 'threshold') {
            $operator = $conditions['operator'] ?? '>';
            $threshold = $conditions['value'] ?? 0;

            return match ($operator) {
                '>' => $val > $threshold,
                '>=' => $val >= $threshold,
                '<' => $val < $threshold,
                '<=' => $val <= $threshold,
                '==' => $val == $threshold,
                default => false,
            };
        }

        if ($rule->type === 'trend') {
            $consecutive = $conditions['consecutive'] ?? 3;
            $direction = $conditions['direction'] ?? 'decline';

            $recentValues = KpiValue::where('kpi_definition_id', $kpi->id)
                ->orderByDesc('recorded_at')
                ->limit($consecutive + 1)
                ->pluck('value')
                ->reverse()
                ->values();

            if ($recentValues->count() < $consecutive + 1) {
                return false;
            }

            $count = 0;
            for ($i = 1; $i < $recentValues->count(); $i++) {
                if ($direction === 'decline' && $recentValues[$i] < $recentValues[$i - 1]) {
                    $count++;
                } elseif ($direction === 'increase' && $recentValues[$i] > $recentValues[$i - 1]) {
                    $count++;
                }
            }

            return $count >= $consecutive;
        }

        return false;
    }

    private function notifyResponsibleUsers(KpiDefinition $kpi, Problem $problem): void
    {
        $users = $kpi->responsibleUsers;
        foreach ($users as $user) {
            $user->notify(new KpiAlertNotification($problem, $kpi));
        }
    }

    private function mapStatusToSeverity(string $status): string
    {
        return match ($status) {
            'critical' => 'critical',
            'warning' => 'medium',
            default => 'low',
        };
    }

    private function stdDev(Collection $values): float
    {
        $mean = $values->avg();
        $sumSquaredDiffs = $values->reduce(fn ($carry, $v) => $carry + pow($v - $mean, 2), 0);
        return sqrt($sumSquaredDiffs / $values->count());
    }
}
