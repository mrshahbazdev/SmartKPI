<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\DailyFocus;
use App\Models\Problem;
use Illuminate\Support\Facades\Auth;

class DailyFocusController extends Controller
{
    /**
     * Auto-generate daily focus for the current user's department.
     */
    public function generate()
    {
        $user = Auth::user();
        $departmentId = $user->department_id;

        if (!$departmentId) {
            return response()->json(['message' => 'No department assigned'], 422);
        }

        $today = now()->toDateString();

        // Check if focus already exists for today
        $existing = DailyFocus::where('department_id', $departmentId)
            ->where('focus_date', $today)
            ->first();

        if ($existing) {
            return response()->json($existing);
        }

        // Find highest priority open action for this department
        $topAction = Action::where('status', 'open')
            ->whereHas('problem', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 END")
            ->with('problem')
            ->first();

        if (!$topAction) {
            // Find highest severity unresolved problem
            $topProblem = Problem::where('department_id', $departmentId)
                ->where('status', 'open')
                ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 END")
                ->first();

            if (!$topProblem) {
                return response()->json(['message' => 'No focus needed — all clear'], 200);
            }

            $focus = DailyFocus::create([
                'department_id' => $departmentId,
                'problem_id' => $topProblem->id,
                'title' => $topProblem->title,
                'description' => $topProblem->description,
                'focus_date' => $today,
                'priority' => $topProblem->severity,
            ]);

            return response()->json($focus);
        }

        $focus = DailyFocus::create([
            'department_id' => $departmentId,
            'action_id' => $topAction->id,
            'problem_id' => $topAction->problem_id,
            'title' => $topAction->title,
            'description' => $topAction->description,
            'focus_date' => $today,
            'priority' => $topAction->priority,
        ]);

        return response()->json($focus);
    }
}
