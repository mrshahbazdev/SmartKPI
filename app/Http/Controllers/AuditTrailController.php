<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->orderByDesc('created_at');

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $activities = $query->paginate(25)->through(function ($activity) {
            return [
                'id' => $activity->id,
                'description' => $activity->description,
                'subject_type' => class_basename($activity->subject_type ?? ''),
                'subject_id' => $activity->subject_id,
                'event' => $activity->event,
                'causer_name' => $activity->causer?->name ?? 'System',
                'properties' => $activity->properties,
                'created_at' => $activity->created_at?->toISOString(),
            ];
        });

        $subjectTypes = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(fn ($t) => class_basename($t));

        return Inertia::render('Settings/AuditTrail', [
            'activities' => $activities,
            'subjectTypes' => $subjectTypes,
            'filters' => $request->only('subject_type', 'causer_id', 'event'),
        ]);
    }
}
