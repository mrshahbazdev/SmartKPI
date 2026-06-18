<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Goal;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $query = Goal::with(['company', 'kpiDefinition', 'assignee'])
            ->orderByDesc('created_at');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $goals = $query->paginate(20)->withQueryString();
        $companies = Company::all(['id', 'name']);

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
            'companies' => $companies,
            'filters' => $request->only(['company_id', 'status']),
        ]);
    }

    public function create()
    {
        $companies = Company::all(['id', 'name']);
        $kpis = KpiDefinition::where('is_template', false)->get(['id', 'name_de', 'name_en', 'company_id', 'unit']);
        $users = User::all(['id', 'name']);

        return Inertia::render('Goals/Create', [
            'companies' => $companies,
            'kpis' => $kpis,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'kpi_definition_id' => ['nullable', 'exists:kpi_definitions,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_value' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        // Auto-fill current value from KPI
        if (isset($validated['kpi_definition_id'])) {
            $latest = KpiValue::where('kpi_definition_id', $validated['kpi_definition_id'])
                ->orderByDesc('recorded_at')
                ->first();
            $validated['current_value'] = $latest ? $latest->value : 0;
        }

        Goal::create($validated);

        return redirect()->route('goals.index')->with('success', __('common.success'));
    }

    public function update(Request $request, Goal $goal)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_value' => ['nullable', 'numeric'],
            'current_value' => ['nullable', 'numeric'],
            'status' => ['sometimes', 'in:active,achieved,missed,cancelled'],
            'progress' => ['sometimes', 'numeric', 'between:0,100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $goal->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroy(Goal $goal)
    {
        $goal->delete();
        return redirect()->route('goals.index')->with('success', __('common.success'));
    }
}
