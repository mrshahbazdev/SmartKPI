<?php

namespace App\Http\Controllers;

use App\Http\Resources\KpiDefinitionResource;
use App\Models\Company;
use App\Models\Department;
use App\Models\KpiDefinition;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KpiDefinitionController extends Controller
{
    public function index(Request $request)
    {
        $query = KpiDefinition::with(['department', 'company', 'latestValue'])
            ->where('is_template', false);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->whereHas('latestValue', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_de', 'ilike', "%{$search}%")
                  ->orWhere('name_en', 'ilike', "%{$search}%");
            });
        }

        $kpis = $query->orderBy('category')->orderBy('name_de')->paginate(20);

        $companies = Company::select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'company_id')->get();

        return Inertia::render('Kpis/Index', [
            'kpis' => KpiDefinitionResource::collection($kpis),
            'companies' => $companies,
            'departments' => $departments,
            'filters' => $request->only(['company_id', 'department_id', 'category', 'status', 'search']),
            'categories' => KpiDefinition::where('is_template', false)
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values(),
        ]);
    }

    public function create()
    {
        $templates = KpiDefinition::where('is_template', true)->get();
        $companies = Company::select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'company_id')->get();

        return Inertia::render('Kpis/Create', [
            'templates' => KpiDefinitionResource::collection($templates),
            'companies' => $companies,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_de' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_de' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'formula' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'target_value' => ['nullable', 'numeric'],
            'warning_threshold' => ['nullable', 'numeric'],
            'critical_threshold' => ['nullable', 'numeric'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'direction' => ['required', 'in:higher_better,lower_better'],
            'category' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        KpiDefinition::create($validated);

        return redirect()->route('kpis.index')->with('success', __('common.success'));
    }

    public function show(KpiDefinition $kpi)
    {
        $kpi->load(['department', 'company', 'values' => function ($q) {
            $q->orderBy('recorded_at', 'desc')->limit(90);
        }]);

        return Inertia::render('Kpis/Show', [
            'kpi' => new KpiDefinitionResource($kpi),
            'values' => $kpi->values->map(function ($v) {
                return [
                    'id' => $v->id,
                    'value' => (float) $v->value,
                    'recorded_at' => $v->recorded_at->format('Y-m-d'),
                    'status' => $v->status,
                    'notes' => $v->notes,
                ];
            }),
        ]);
    }

    public function edit(KpiDefinition $kpi)
    {
        $companies = Company::select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'company_id')->get();

        return Inertia::render('Kpis/Edit', [
            'kpi' => new KpiDefinitionResource($kpi),
            'companies' => $companies,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, KpiDefinition $kpi)
    {
        $validated = $request->validate([
            'name_de' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_de' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'formula' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'target_value' => ['nullable', 'numeric'],
            'warning_threshold' => ['nullable', 'numeric'],
            'critical_threshold' => ['nullable', 'numeric'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'direction' => ['required', 'in:higher_better,lower_better'],
            'category' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $kpi->update($validated);

        return redirect()->route('kpis.show', $kpi)->with('success', __('common.success'));
    }

    public function destroy(KpiDefinition $kpi)
    {
        $kpi->delete();
        return redirect()->route('kpis.index')->with('success', __('common.success'));
    }

    public function templates()
    {
        $templates = KpiDefinition::where('is_template', true)
            ->orderBy('category')
            ->get();

        $companies = Company::select('id', 'name')->get();
        $departments = Department::select('id', 'name', 'company_id')->get();

        return Inertia::render('Kpis/Templates', [
            'templates' => KpiDefinitionResource::collection($templates),
            'companies' => $companies,
            'departments' => $departments,
        ]);
    }

    public function useTemplate(Request $request, KpiDefinition $template)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'target_value' => ['nullable', 'numeric'],
            'warning_threshold' => ['nullable', 'numeric'],
            'critical_threshold' => ['nullable', 'numeric'],
        ]);

        $kpi = $template->replicate();
        $kpi->is_template = false;
        $kpi->company_id = $validated['company_id'];
        $kpi->department_id = $validated['department_id'];
        $kpi->target_value = $validated['target_value'] ?? $template->target_value;
        $kpi->warning_threshold = $validated['warning_threshold'] ?? $template->warning_threshold;
        $kpi->critical_threshold = $validated['critical_threshold'] ?? $template->critical_threshold;
        $kpi->save();

        return redirect()->route('kpis.show', $kpi)->with('success', __('common.success'));
    }
}
