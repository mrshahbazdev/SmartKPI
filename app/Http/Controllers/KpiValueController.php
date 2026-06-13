<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Services\ProblemDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KpiValueController extends Controller
{
    public function store(Request $request, KpiDefinition $kpi)
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

        return redirect()->back()->with('success', __('common.success'));
    }

    public function bulkStore(Request $request, KpiDefinition $kpi)
    {
        $validated = $request->validate([
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.value' => ['required', 'numeric'],
            'entries.*.recorded_at' => ['required', 'date'],
            'entries.*.notes' => ['nullable', 'string'],
        ]);

        foreach ($validated['entries'] as $entry) {
            $status = $this->calculateStatus($kpi, (float) $entry['value']);
            KpiValue::create([
                'kpi_definition_id' => $kpi->id,
                'value' => $entry['value'],
                'recorded_at' => $entry['recorded_at'],
                'notes' => $entry['notes'] ?? null,
                'recorded_by' => Auth::id(),
                'status' => $status,
            ]);
        }

        return redirect()->back()->with('success', __('common.success'));
    }

    public function update(Request $request, KpiValue $value)
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'recorded_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $status = $this->calculateStatus($value->kpiDefinition, (float) $validated['value']);
        $validated['status'] = $status;

        $value->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroy(KpiValue $value)
    {
        $value->delete();
        return redirect()->back()->with('success', __('common.success'));
    }

    public function import(Request $request, KpiDefinition $kpi)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls'],
            'date_column' => ['required', 'string'],
            'value_column' => ['required', 'string'],
            'notes_column' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $rows = [];
        if (in_array($extension, ['csv', 'txt'])) {
            $handle = fopen($file->getRealPath(), 'r');
            $headers = fgetcsv($handle, 0, ';');
            if (!$headers) {
                $headers = fgetcsv($handle, 0, ',');
                rewind($handle);
                $headers = fgetcsv($handle, 0, ',');
            }

            $dateIdx = array_search($request->date_column, $headers);
            $valueIdx = array_search($request->value_column, $headers);
            $notesIdx = $request->notes_column ? array_search($request->notes_column, $headers) : false;

            while (($row = fgetcsv($handle, 0, str_contains(implode('', $headers), ';') ? ';' : ',')) !== false) {
                if ($dateIdx === false || $valueIdx === false) continue;
                $rows[] = [
                    'recorded_at' => $row[$dateIdx] ?? null,
                    'value' => $row[$valueIdx] ?? null,
                    'notes' => $notesIdx !== false ? ($row[$notesIdx] ?? null) : null,
                ];
            }
            fclose($handle);
        }

        $imported = 0;
        foreach ($rows as $row) {
            if (!$row['recorded_at'] || !is_numeric(str_replace(',', '.', $row['value']))) continue;
            $numValue = (float) str_replace(',', '.', $row['value']);
            $status = $this->calculateStatus($kpi, $numValue);

            KpiValue::create([
                'kpi_definition_id' => $kpi->id,
                'value' => $numValue,
                'recorded_at' => $row['recorded_at'],
                'notes' => $row['notes'],
                'recorded_by' => Auth::id(),
                'status' => $status,
            ]);
            $imported++;
        }

        return redirect()->back()->with('success', "{$imported} " . __('kpi.values_imported'));
    }

    private function calculateStatus(KpiDefinition $kpi, float $value): string
    {
        if (!$kpi->target_value) {
            return 'on_target';
        }

        if ($kpi->direction === 'higher_better') {
            if ($kpi->critical_threshold && $value <= $kpi->critical_threshold) return 'critical';
            if ($kpi->warning_threshold && $value <= $kpi->warning_threshold) return 'warning';
            return 'on_target';
        }

        // lower_better
        if ($kpi->critical_threshold && $value >= $kpi->critical_threshold) return 'critical';
        if ($kpi->warning_threshold && $value >= $kpi->warning_threshold) return 'warning';
        return 'on_target';
    }
}
