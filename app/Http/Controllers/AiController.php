<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use App\Models\AiInsight;
use App\Models\Company;
use App\Models\KpiDefinition;
use App\Models\Problem;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AiController extends Controller
{
    public function __construct(
        private AiService $aiService
    ) {}

    // ─── Dashboard ──────────────────────────────────────────────────────────

    public function dashboard()
    {
        $insights = AiInsight::where('status', 'active')
            ->latest()
            ->limit(20)
            ->get();

        $insightsByType = $insights->groupBy('type');

        $stats = [
            'total_insights' => AiInsight::count(),
            'active_insights' => AiInsight::where('status', 'active')->count(),
            'anomalies_detected' => AiInsight::where('type', 'anomaly')->count(),
            'recommendations' => AiInsight::where('type', 'recommendation')->count(),
            'root_causes' => AiInsight::where('type', 'root_cause')->count(),
            'action_suggestions' => AiInsight::where('type', 'action_suggestion')->count(),
        ];

        return Inertia::render('Ai/Dashboard', [
            'insights' => $insights,
            'insightsByType' => $insightsByType,
            'stats' => $stats,
        ]);
    }

    // ─── Anomaly Detection ──────────────────────────────────────────────────

    public function anomalies()
    {
        $anomalies = AiInsight::where('type', 'anomaly')
            ->with('kpiDefinition')
            ->latest()
            ->paginate(20);

        $kpis = KpiDefinition::where('is_active', true)
            ->get(['id', 'name_de', 'name_en']);

        return Inertia::render('Ai/Anomalies', [
            'anomalies' => $anomalies,
            'kpis' => $kpis,
        ]);
    }

    public function detectAnomalies(Request $request)
    {
        $request->validate(['kpi_id' => 'required|exists:kpi_definitions,id']);
        $kpi = KpiDefinition::findOrFail($request->kpi_id);
        $insight = $this->aiService->detectAnomalies($kpi);

        return back()->with('success', $insight
            ? __('AI anomaly analysis complete.')
            : __('No anomalies detected.')
        );
    }

    // ─── KPI Recommendations ────────────────────────────────────────────────

    public function recommendations()
    {
        $recommendations = AiInsight::where('type', 'recommendation')
            ->with('company')
            ->latest()
            ->paginate(20);

        $companies = Company::all(['id', 'name']);

        return Inertia::render('Ai/Recommendations', [
            'recommendations' => $recommendations,
            'companies' => $companies,
        ]);
    }

    public function generateRecommendations(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $company = Company::findOrFail($request->company_id);
        $insights = $this->aiService->recommendKpis($company);

        return back()->with('success', count($insights) . ' KPI recommendations generated.');
    }

    // ─── Natural Language Insights ──────────────────────────────────────────

    public function insights()
    {
        $insights = AiInsight::where('type', 'insight')
            ->with(['kpiDefinition', 'company', 'department'])
            ->latest()
            ->paginate(20);

        $kpis = KpiDefinition::where('is_active', true)
            ->get(['id', 'name_de', 'name_en']);

        return Inertia::render('Ai/Insights', [
            'insights' => $insights,
            'kpis' => $kpis,
        ]);
    }

    public function generateInsight(Request $request)
    {
        $request->validate(['kpi_id' => 'required|exists:kpi_definitions,id']);
        $kpi = KpiDefinition::findOrFail($request->kpi_id);
        $insight = $this->aiService->generateInsights($kpi);

        return back()->with('success', $insight
            ? __('AI insight report generated.')
            : __('Not enough data for analysis.')
        );
    }

    // ─── Predictive Actions ─────────────────────────────────────────────────

    public function actionSuggestions()
    {
        $suggestions = AiInsight::where('type', 'action_suggestion')
            ->with(['problem', 'kpiDefinition'])
            ->latest()
            ->paginate(20);

        $problems = Problem::where('status', 'open')
            ->get(['id', 'title', 'severity']);

        return Inertia::render('Ai/ActionSuggestions', [
            'suggestions' => $suggestions,
            'problems' => $problems,
        ]);
    }

    public function suggestActions(Request $request)
    {
        $request->validate(['problem_id' => 'required|exists:problems,id']);
        $problem = Problem::findOrFail($request->problem_id);
        $insights = $this->aiService->suggestActions($problem);

        return back()->with('success', count($insights) . ' action suggestions generated.');
    }

    // ─── Root Cause Analysis ────────────────────────────────────────────────

    public function rootCauses()
    {
        $analyses = AiInsight::where('type', 'root_cause')
            ->with(['problem', 'kpiDefinition'])
            ->latest()
            ->paginate(20);

        $problems = Problem::where('status', 'open')
            ->get(['id', 'title', 'severity']);

        return Inertia::render('Ai/RootCauses', [
            'analyses' => $analyses,
            'problems' => $problems,
        ]);
    }

    public function analyzeRootCause(Request $request)
    {
        $request->validate(['problem_id' => 'required|exists:problems,id']);
        $problem = Problem::findOrFail($request->problem_id);
        $insight = $this->aiService->analyzeRootCause($problem);

        return back()->with('success', $insight
            ? __('Root cause analysis complete.')
            : __('Could not perform analysis.')
        );
    }

    // ─── AI Chatbot ─────────────────────────────────────────────────────────

    public function chatbot()
    {
        $sessionId = request()->query('session', Str::uuid()->toString());

        $messages = AiChatMessage::where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->orderBy('id')
            ->get();

        $sessions = AiChatMessage::where('user_id', auth()->id())
            ->selectRaw('session_id, MIN(created_at) as started_at, COUNT(*) as message_count')
            ->groupBy('session_id')
            ->orderByDesc('started_at')
            ->limit(20)
            ->get();

        return Inertia::render('Ai/Chatbot', [
            'messages' => $messages,
            'sessionId' => $sessionId,
            'sessions' => $sessions,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'required|string',
        ]);

        $reply = $this->aiService->chat_query(
            auth()->user(),
            $request->message,
            $request->session_id
        );

        return back();
    }

    // ─── Shared Actions ─────────────────────────────────────────────────────

    public function dismissInsight(AiInsight $insight)
    {
        $insight->update(['status' => 'dismissed']);
        return back()->with('success', __('Insight dismissed.'));
    }

    public function applyInsight(AiInsight $insight)
    {
        $insight->update(['status' => 'applied']);
        return back()->with('success', __('Insight marked as applied.'));
    }
}
