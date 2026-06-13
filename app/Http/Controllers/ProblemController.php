<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        $query = Problem::with(['kpiDefinition', 'department', 'assignee'])
            ->orderByDesc('detected_at');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('company_id')) {
            $deptIds = Department::where('company_id', $request->company_id)->pluck('id');
            $query->whereIn('department_id', $deptIds);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $problems = $query->paginate(20)->withQueryString();
        $companies = Company::all(['id', 'name']);
        $departments = Department::all(['id', 'company_id', 'name']);
        $users = User::all(['id', 'name']);

        return Inertia::render('Problems/Index', [
            'problems' => $problems,
            'companies' => $companies,
            'departments' => $departments,
            'users' => $users,
            'filters' => $request->only(['department_id', 'company_id', 'severity', 'status', 'assigned_to', 'search']),
        ]);
    }

    public function show(Problem $problem)
    {
        $problem->load(['kpiDefinition.values' => function ($q) {
            $q->orderByDesc('recorded_at')->limit(30);
        }, 'department', 'assignee', 'rootCauses.kpiRelationship', 'actions.assignee']);

        $users = User::all(['id', 'name']);

        return Inertia::render('Problems/Show', [
            'problem' => $problem,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:open,investigating,resolved,closed'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $problem->update($validated);

        if (isset($validated['status']) && in_array($validated['status'], ['resolved', 'closed'])) {
            $problem->update(['resolved_at' => now()]);
        }

        return redirect()->back()->with('success', __('common.success'));
    }

    public function assign(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $problem->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function timeline(Request $request)
    {
        $query = Problem::with(['kpiDefinition', 'department', 'assignee'])
            ->orderByDesc('detected_at');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('company_id')) {
            $deptIds = Department::where('company_id', $request->company_id)->pluck('id');
            $query->whereIn('department_id', $deptIds);
        }

        $problems = $query->limit(50)->get();
        $companies = Company::all(['id', 'name']);
        $departments = Department::all(['id', 'company_id', 'name']);

        return Inertia::render('Problems/Timeline', [
            'problems' => $problems,
            'companies' => $companies,
            'departments' => $departments,
            'filters' => $request->only(['department_id', 'company_id']),
        ]);
    }
}
