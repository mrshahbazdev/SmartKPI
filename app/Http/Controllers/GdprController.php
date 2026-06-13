<?php

namespace App\Http\Controllers;

use App\Models\ConsentRecord;
use App\Models\DataExportRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GdprController extends Controller
{
    public function index()
    {
        $consents = ConsentRecord::where('user_id', Auth::id())
            ->orderBy('consent_type')
            ->get();

        $exports = DataExportRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Settings/Gdpr', [
            'consents' => $consents,
            'exports' => $exports,
        ]);
    }

    public function updateConsent(Request $request)
    {
        $validated = $request->validate([
            'consent_type' => ['required', 'in:privacy_policy,data_processing,marketing,analytics'],
            'consented' => ['required', 'boolean'],
        ]);

        ConsentRecord::updateOrCreate(
            ['user_id' => Auth::id(), 'consent_type' => $validated['consent_type']],
            [
                'consented' => $validated['consented'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'consented_at' => $validated['consented'] ? now() : null,
                'withdrawn_at' => !$validated['consented'] ? now() : null,
            ]
        );

        return redirect()->back()->with('success', __('common.success'));
    }

    public function requestExport()
    {
        $existing = DataExportRequest::where('user_id', Auth::id())
            ->where('type', 'export')
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Export already in progress');
        }

        $exportRequest = DataExportRequest::create([
            'user_id' => Auth::id(),
            'type' => 'export',
            'status' => 'processing',
        ]);

        // Generate export immediately (in production, queue this)
        $this->generateExport($exportRequest);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function downloadExport(DataExportRequest $export)
    {
        if ($export->user_id !== Auth::id() || !$export->file_path) {
            abort(403);
        }

        return Storage::download($export->file_path);
    }

    public function requestDeletion()
    {
        DataExportRequest::create([
            'user_id' => Auth::id(),
            'type' => 'deletion',
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', __('gdpr.deletion_requested'));
    }

    private function generateExport(DataExportRequest $exportRequest): void
    {
        $user = User::find($exportRequest->user_id);
        if (!$user) return;

        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'locale' => $user->locale,
                'created_at' => $user->created_at?->toISOString(),
            ],
            'consents' => ConsentRecord::where('user_id', $user->id)->get()->toArray(),
            'kpi_ownership' => $user->ownedKpis()->get()->map(fn ($k) => [
                'kpi' => $k->name_de . ' / ' . $k->name_en,
                'role' => $k->pivot->role,
            ])->toArray(),
            'assigned_problems' => $user->assignedProblems()->get()->map(fn ($p) => [
                'title' => $p->title,
                'severity' => $p->severity,
                'status' => $p->status,
                'detected_at' => $p->detected_at?->toISOString(),
            ])->toArray(),
            'assigned_actions' => $user->assignedActions()->get()->map(fn ($a) => [
                'title' => $a->title,
                'status' => $a->status,
                'deadline' => $a->deadline,
            ])->toArray(),
            'exported_at' => now()->toISOString(),
        ];

        $filename = 'exports/user-' . $user->id . '-' . now()->format('Y-m-d-His') . '.json';
        Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $exportRequest->update([
            'status' => 'completed',
            'file_path' => $filename,
            'completed_at' => now(),
        ]);
    }
}
