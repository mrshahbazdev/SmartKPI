<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\ActionResult;
use App\Models\KpiValue;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActionController extends Controller
{
    public function index(Request $request)
    {
        $query = Action::with(['problem.kpiDefinition', 'assignee', 'creator'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        // Show user's own actions by default
        if (!$request->filled('show_all')) {
            $query->where('assigned_to', Auth::id());
        }

        $actions = $query->paginate(20)->withQueryString();
        $users = User::all(['id', 'name']);

        return Inertia::render('Actions/Index', [
            'actions' => $actions,
            'users' => $users,
            'filters' => $request->only(['status', 'assigned_to', 'priority', 'search', 'show_all']),
        ]);
    }

    public function create(Request $request)
    {
        $problems = Problem::where('status', '!=', 'closed')
            ->with('kpiDefinition')
            ->get();
        $users = User::all(['id', 'name']);

        return Inertia::render('Actions/Create', [
            'problems' => $problems,
            'users' => $users,
            'problem_id' => $request->problem_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'problem_id' => ['nullable', 'exists:problems,id'],
            'root_cause_id' => ['nullable', 'exists:root_causes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['required', 'exists:users,id'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'deadline' => ['nullable', 'date'],
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'open';
        $validated['source'] = 'manual';

        Action::create($validated);

        return redirect()->route('actions.index')->with('success', __('common.success'));
    }

    public function show(Action $action)
    {
        $action->load(['problem.kpiDefinition', 'rootCause', 'assignee', 'creator', 'results']);

        // Get before/after KPI values for effectiveness
        $effectiveness = null;
        if ($action->problem && $action->problem->kpiDefinition) {
            $kpi = $action->problem->kpiDefinition;
            $beforeValue = KpiValue::where('kpi_definition_id', $kpi->id)
                ->where('recorded_at', '<=', $action->created_at)
                ->orderByDesc('recorded_at')
                ->first();

            $afterValue = KpiValue::where('kpi_definition_id', $kpi->id)
                ->orderByDesc('recorded_at')
                ->first();

            if ($beforeValue && $afterValue) {
                $effectiveness = [
                    'before' => (float) $beforeValue->value,
                    'after' => (float) $afterValue->value,
                    'change' => (float) $afterValue->value - (float) $beforeValue->value,
                    'change_pct' => $beforeValue->value != 0
                        ? round(((float) $afterValue->value - (float) $beforeValue->value) / (float) $beforeValue->value * 100, 1)
                        : null,
                    'kpi_name_de' => $kpi->name_de,
                    'kpi_name_en' => $kpi->name_en,
                    'unit' => $kpi->unit,
                    'direction' => $kpi->direction,
                ];
            }
        }

        $users = User::all(['id', 'name']);

        return Inertia::render('Actions/Show', [
            'action' => $action,
            'effectiveness' => $effectiveness,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Action $action)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['sometimes', 'exists:users,id'],
            'status' => ['sometimes', 'in:open,in_progress,completed,cancelled'],
            'priority' => ['sometimes', 'in:low,medium,high,critical'],
            'deadline' => ['nullable', 'date'],
        ]);

        $action->update($validated);

        // If completed, record result
        if (isset($validated['status']) && $validated['status'] === 'completed' && $action->problem) {
            $kpi = $action->problem->kpiDefinition;
            if ($kpi) {
                $latestValue = KpiValue::where('kpi_definition_id', $kpi->id)
                    ->orderByDesc('recorded_at')
                    ->first();
                $beforeValue = KpiValue::where('kpi_definition_id', $kpi->id)
                    ->where('recorded_at', '<=', $action->created_at)
                    ->orderByDesc('recorded_at')
                    ->first();

                if ($latestValue && $beforeValue) {
                    $change = (float) $latestValue->value - (float) $beforeValue->value;
                    $isImprovement = ($kpi->direction === 'higher_better' && $change > 0)
                        || ($kpi->direction === 'lower_better' && $change < 0);

                    ActionResult::create([
                        'action_id' => $action->id,
                        'kpi_value_before' => $beforeValue->value,
                        'kpi_value_after' => $latestValue->value,
                        'effectiveness_score' => $isImprovement ? min(abs($change), 100) : 0,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroy(Action $action)
    {
        $action->delete();
        return redirect()->route('actions.index')->with('success', __('common.success'));
    }
}
