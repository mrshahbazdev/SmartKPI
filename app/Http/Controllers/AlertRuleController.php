<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use App\Models\Company;
use App\Models\KpiDefinition;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlertRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = AlertRule::with(['company', 'kpiDefinition', 'notifyUser', 'escalateToUser']);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $rules = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $companies = Company::all(['id', 'name']);
        $kpis = KpiDefinition::where('is_template', false)->get(['id', 'name_de', 'name_en', 'company_id']);
        $users = User::all(['id', 'name']);

        return Inertia::render('AlertRules/Index', [
            'rules' => $rules,
            'companies' => $companies,
            'kpis' => $kpis,
            'users' => $users,
            'filters' => $request->only(['company_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'kpi_definition_id' => ['nullable', 'exists:kpi_definitions,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:threshold,trend,anomaly'],
            'conditions' => ['required', 'array'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'is_active' => ['boolean'],
            'auto_create_problem' => ['boolean'],
            'notify_user_id' => ['nullable', 'exists:users,id'],
            'escalation_hours' => ['nullable', 'integer', 'min:1'],
            'escalate_to_user_id' => ['nullable', 'exists:users,id'],
        ]);

        AlertRule::create($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function update(Request $request, AlertRule $alertRule)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:threshold,trend,anomaly'],
            'conditions' => ['sometimes', 'array'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'is_active' => ['boolean'],
            'auto_create_problem' => ['boolean'],
            'notify_user_id' => ['nullable', 'exists:users,id'],
            'escalation_hours' => ['nullable', 'integer', 'min:1'],
            'escalate_to_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $alertRule->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroy(AlertRule $alertRule)
    {
        $alertRule->delete();
        return redirect()->back()->with('success', __('common.success'));
    }
}
