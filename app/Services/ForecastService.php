<?php

namespace App\Services;

use App\Models\Forecast;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use Illuminate\Support\Collection;

class ForecastService
{
    /**
     * Generate forecasts for a KPI using multiple methods.
     */
    public function generateForecast(KpiDefinition $kpi, string $horizon = '30d'): array
    {
        $days = match ($horizon) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $values = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderBy('recorded_at')
            ->pluck('value', 'recorded_at')
            ->map(fn ($v) => (float) $v);

        if ($values->count() < 5) {
            return [];
        }

        // Delete old forecasts for this KPI + horizon
        Forecast::where('kpi_definition_id', $kpi->id)
            ->where('horizon', $horizon)
            ->delete();

        $forecasts = [];
        $numericValues = $values->values()->toArray();
        $lastDate = $values->keys()->last();

        // Linear regression forecast
        $linearPredictions = $this->linearRegression($numericValues, $days);

        // Moving average forecast
        $maPredictions = $this->movingAverage($numericValues, $days);

        // Exponential smoothing
        $esPredictions = $this->exponentialSmoothing($numericValues, $days);

        // Ensemble: average of all methods
        for ($i = 0; $i < $days; $i++) {
            $forecastDate = now()->addDays($i + 1)->toDateString();
            $predicted = ($linearPredictions[$i] + $maPredictions[$i] + $esPredictions[$i]) / 3;

            // Confidence interval based on historical standard deviation
            $stdDev = $this->stdDev(collect($numericValues));
            $lower = $predicted - (1.96 * $stdDev);
            $upper = $predicted + (1.96 * $stdDev);

            $forecast = Forecast::create([
                'kpi_definition_id' => $kpi->id,
                'forecast_date' => $forecastDate,
                'predicted_value' => round($predicted, 4),
                'lower_bound' => round($lower, 4),
                'upper_bound' => round($upper, 4),
                'confidence' => 0.80,
                'horizon' => $horizon,
                'method' => 'ensemble',
            ]);

            $forecasts[] = $forecast;
        }

        return $forecasts;
    }

    /**
     * Early warning: predict when KPI will breach threshold.
     */
    public function earlyWarning(KpiDefinition $kpi): ?array
    {
        $values = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderBy('recorded_at')
            ->pluck('value')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        if (count($values) < 5 || !$kpi->critical_threshold) {
            return null;
        }

        $predictions = $this->linearRegression($values, 90);
        $threshold = (float) $kpi->critical_threshold;

        for ($i = 0; $i < count($predictions); $i++) {
            $breaches = false;
            if ($kpi->direction === 'higher_better' && $predictions[$i] <= $threshold) {
                $breaches = true;
            }
            if ($kpi->direction === 'lower_better' && $predictions[$i] >= $threshold) {
                $breaches = true;
            }

            if ($breaches) {
                return [
                    'days_until_breach' => $i + 1,
                    'breach_date' => now()->addDays($i + 1)->toDateString(),
                    'predicted_value' => round($predictions[$i], 2),
                    'threshold' => $threshold,
                    'kpi_name_de' => $kpi->name_de,
                    'kpi_name_en' => $kpi->name_en,
                ];
            }
        }

        return null;
    }

    private function linearRegression(array $values, int $forecastDays): array
    {
        $n = count($values);
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $values[$i];
            $sumXY += $i * $values[$i];
            $sumX2 += $i * $i;
        }

        $denom = ($n * $sumX2 - $sumX * $sumX);
        if ($denom == 0) {
            return array_fill(0, $forecastDays, end($values));
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denom;
        $intercept = ($sumY - $slope * $sumX) / $n;

        $predictions = [];
        for ($i = 0; $i < $forecastDays; $i++) {
            $predictions[] = $intercept + $slope * ($n + $i);
        }

        return $predictions;
    }

    private function movingAverage(array $values, int $forecastDays): array
    {
        $window = min(7, count($values));
        $lastWindow = array_slice($values, -$window);
        $ma = array_sum($lastWindow) / $window;

        return array_fill(0, $forecastDays, $ma);
    }

    private function exponentialSmoothing(array $values, int $forecastDays, float $alpha = 0.3): array
    {
        $smoothed = $values[0];
        foreach ($values as $v) {
            $smoothed = $alpha * $v + (1 - $alpha) * $smoothed;
        }

        // Simple trend from last smoothed values
        $n = count($values);
        $trend = 0;
        if ($n >= 2) {
            $prevSmoothed = $values[0];
            $currentSmoothed = $values[0];
            foreach ($values as $v) {
                $prevSmoothed = $currentSmoothed;
                $currentSmoothed = $alpha * $v + (1 - $alpha) * $currentSmoothed;
            }
            $trend = $currentSmoothed - $prevSmoothed;
        }

        $predictions = [];
        for ($i = 0; $i < $forecastDays; $i++) {
            $predictions[] = $smoothed + $trend * ($i + 1);
        }

        return $predictions;
    }

    private function stdDev(Collection $values): float
    {
        $mean = $values->avg();
        $sumSquaredDiffs = $values->reduce(fn ($carry, $v) => $carry + pow($v - $mean, 2), 0);
        return sqrt($sumSquaredDiffs / max($values->count(), 1));
    }
}
