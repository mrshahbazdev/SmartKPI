<?php

namespace App\Services;

use App\Models\Action;
use App\Models\AiChatMessage;
use App\Models\AiInsight;
use App\Models\Company;
use App\Models\Department;
use App\Models\KpiDefinition;
use App\Models\KpiRelationship;
use App\Models\KpiValue;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AiService
{
    private string $model = 'gpt-4o-mini';

    // ─── 1. AI Anomaly Detection ────────────────────────────────────────────

    public function detectAnomalies(KpiDefinition $kpi): ?AiInsight
    {
        $values = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderBy('recorded_at')
            ->get(['value', 'recorded_at'])
            ->map(fn ($v) => ['date' => $v->recorded_at->format('Y-m-d'), 'value' => (float) $v->value]);

        if ($values->count() < 10) {
            return null;
        }

        $prompt = $this->buildPrompt('anomaly_detection', [
            'kpi_name' => $kpi->name_de . ' / ' . $kpi->name_en,
            'unit' => $kpi->unit,
            'direction' => $kpi->direction,
            'target' => $kpi->target_value,
            'warning_threshold' => $kpi->warning_threshold,
            'critical_threshold' => $kpi->critical_threshold,
            'values' => $values->toJson(),
        ]);

        $response = $this->chat($prompt);
        if (!$response) {
            return null;
        }

        $parsed = $this->parseJson($response);
        if (!$parsed || !($parsed['is_anomaly'] ?? false)) {
            return null;
        }

        return AiInsight::create([
            'kpi_definition_id' => $kpi->id,
            'department_id' => $kpi->department_id,
            'company_id' => $kpi->company_id,
            'type' => 'anomaly',
            'title_de' => $parsed['title_de'] ?? 'KI-Anomalie erkannt',
            'title_en' => $parsed['title_en'] ?? 'AI Anomaly Detected',
            'content_de' => $parsed['explanation_de'] ?? $parsed['explanation'] ?? '',
            'content_en' => $parsed['explanation_en'] ?? $parsed['explanation'] ?? '',
            'severity' => $parsed['severity'] ?? 'warning',
            'metadata' => [
                'confidence' => $parsed['confidence'] ?? 0.7,
                'anomaly_type' => $parsed['anomaly_type'] ?? 'unknown',
                'affected_dates' => $parsed['affected_dates'] ?? [],
                'model' => $this->model,
            ],
        ]);
    }

    // ─── 2. Smart KPI Recommendations ───────────────────────────────────────

    public function recommendKpis(Company $company): array
    {
        $existingKpis = KpiDefinition::where('company_id', $company->id)
            ->get(['name_de', 'name_en', 'category', 'unit']);

        $departments = Department::where('company_id', $company->id)
            ->pluck('name')
            ->toArray();

        $prompt = $this->buildPrompt('kpi_recommendations', [
            'company_name' => $company->name,
            'departments' => implode(', ', $departments),
            'existing_kpis' => $existingKpis->map(fn ($k) => $k->name_de . ' (' . $k->category . ')')->implode(', '),
        ]);

        $response = $this->chat($prompt);
        if (!$response) {
            return [];
        }

        $parsed = $this->parseJson($response);
        $recommendations = $parsed['recommendations'] ?? [];
        $insights = [];

        foreach (array_slice($recommendations, 0, 10) as $rec) {
            $insights[] = AiInsight::create([
                'company_id' => $company->id,
                'type' => 'recommendation',
                'title_de' => $rec['name_de'] ?? $rec['name'] ?? 'Empfehlung',
                'title_en' => $rec['name_en'] ?? $rec['name'] ?? 'Recommendation',
                'content_de' => $rec['reason_de'] ?? $rec['reason'] ?? '',
                'content_en' => $rec['reason_en'] ?? $rec['reason'] ?? '',
                'severity' => 'info',
                'metadata' => [
                    'category' => $rec['category'] ?? 'general',
                    'unit' => $rec['unit'] ?? '%',
                    'target_value' => $rec['target_value'] ?? null,
                    'direction' => $rec['direction'] ?? 'higher_better',
                    'department' => $rec['department'] ?? null,
                    'priority' => $rec['priority'] ?? 'medium',
                    'model' => $this->model,
                ],
            ]);
        }

        return $insights;
    }

    // ─── 3. Natural Language Insights ────────────────────────────────────────

    public function generateInsights(KpiDefinition $kpi): ?AiInsight
    {
        $values = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderBy('recorded_at')
            ->get(['value', 'recorded_at', 'status'])
            ->map(fn ($v) => [
                'date' => $v->recorded_at->format('Y-m-d'),
                'value' => (float) $v->value,
                'status' => $v->status,
            ]);

        if ($values->count() < 5) {
            return null;
        }

        $problems = Problem::where('kpi_definition_id', $kpi->id)
            ->latest()
            ->limit(5)
            ->get(['title', 'severity', 'status', 'detected_at']);

        $prompt = $this->buildPrompt('natural_language_insights', [
            'kpi_name_de' => $kpi->name_de,
            'kpi_name_en' => $kpi->name_en,
            'unit' => $kpi->unit,
            'target' => $kpi->target_value,
            'direction' => $kpi->direction,
            'values' => $values->toJson(),
            'recent_problems' => $problems->toJson(),
            'department' => $kpi->department->name ?? 'N/A',
            'company' => $kpi->company->name ?? 'N/A',
        ]);

        $response = $this->chat($prompt);
        if (!$response) {
            return null;
        }

        $parsed = $this->parseJson($response);

        return AiInsight::create([
            'kpi_definition_id' => $kpi->id,
            'department_id' => $kpi->department_id,
            'company_id' => $kpi->company_id,
            'type' => 'insight',
            'title_de' => $parsed['title_de'] ?? 'KI-Analyse für ' . $kpi->name_de,
            'title_en' => $parsed['title_en'] ?? 'AI Analysis for ' . $kpi->name_en,
            'content_de' => $parsed['report_de'] ?? '',
            'content_en' => $parsed['report_en'] ?? '',
            'severity' => $parsed['overall_status'] ?? 'info',
            'metadata' => [
                'trend' => $parsed['trend'] ?? 'stable',
                'performance_score' => $parsed['performance_score'] ?? null,
                'key_findings' => $parsed['key_findings'] ?? [],
                'recommendations' => $parsed['recommendations'] ?? [],
                'model' => $this->model,
            ],
        ]);
    }

    // ─── 4. Predictive Actions ──────────────────────────────────────────────

    public function suggestActions(Problem $problem): array
    {
        $kpi = $problem->kpiDefinition;
        $department = $problem->department;

        $pastProblems = Problem::where('kpi_definition_id', $kpi->id)
            ->where('status', 'resolved')
            ->with('actions')
            ->limit(10)
            ->get();

        $pastActions = $pastProblems->flatMap(fn ($p) => $p->actions)->map(fn ($a) => [
            'title' => $a->title,
            'priority' => $a->priority,
            'status' => $a->status,
            'effectiveness' => $a->effectiveness_score ?? null,
        ]);

        $prompt = $this->buildPrompt('predictive_actions', [
            'problem_title' => $problem->title,
            'problem_description' => $problem->description,
            'problem_severity' => $problem->severity,
            'kpi_name_de' => $kpi->name_de,
            'kpi_name_en' => $kpi->name_en,
            'department' => $department->name ?? 'N/A',
            'past_actions' => $pastActions->toJson(),
        ]);

        $response = $this->chat($prompt);
        if (!$response) {
            return [];
        }

        $parsed = $this->parseJson($response);
        $suggestions = $parsed['actions'] ?? [];
        $insights = [];

        foreach (array_slice($suggestions, 0, 5) as $suggestion) {
            $insights[] = AiInsight::create([
                'problem_id' => $problem->id,
                'kpi_definition_id' => $kpi->id,
                'department_id' => $problem->department_id,
                'type' => 'action_suggestion',
                'title_de' => $suggestion['title_de'] ?? $suggestion['title'] ?? 'Vorgeschlagene Maßnahme',
                'title_en' => $suggestion['title_en'] ?? $suggestion['title'] ?? 'Suggested Action',
                'content_de' => $suggestion['description_de'] ?? $suggestion['description'] ?? '',
                'content_en' => $suggestion['description_en'] ?? $suggestion['description'] ?? '',
                'severity' => 'info',
                'metadata' => [
                    'priority' => $suggestion['priority'] ?? 'medium',
                    'estimated_impact' => $suggestion['estimated_impact'] ?? null,
                    'estimated_days' => $suggestion['estimated_days'] ?? null,
                    'confidence' => $suggestion['confidence'] ?? 0.7,
                    'based_on_past' => $suggestion['based_on_past'] ?? false,
                    'model' => $this->model,
                ],
            ]);
        }

        return $insights;
    }

    // ─── 5. AI Chatbot ──────────────────────────────────────────────────────

    public function chat_query(User $user, string $message, string $sessionId): AiChatMessage
    {
        // Save user message
        AiChatMessage::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $message,
        ]);

        // Build context from user's accessible data
        $context = $this->buildChatContext($user);

        // Get recent chat history
        $history = AiChatMessage::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $locale = app()->getLocale();
        $systemPrompt = "You are SmartKPI AI Assistant — an intelligent KPI analysis assistant. " .
            "Respond in " . ($locale === 'de' ? 'German' : 'English') . ". " .
            "You have access to the user's KPI data and can analyze trends, suggest improvements, and answer questions about performance metrics. " .
            "Be concise, data-driven, and actionable. Use numbers and percentages when possible.\n\n" .
            "USER CONTEXT:\n" . $context;

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history
        );

        try {
            $result = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            $reply = $result->choices[0]->message->content ?? 'Entschuldigung, ich konnte keine Antwort generieren.';
        } catch (\Exception $e) {
            Log::error('AI Chat error: ' . $e->getMessage());
            $reply = $locale === 'de'
                ? 'Entschuldigung, der KI-Service ist momentan nicht verfügbar. Bitte versuchen Sie es später erneut.'
                : 'Sorry, the AI service is currently unavailable. Please try again later.';
        }

        return AiChatMessage::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'role' => 'assistant',
            'content' => $reply,
        ]);
    }

    // ─── 6. Auto Root Cause Analysis ────────────────────────────────────────

    public function analyzeRootCause(Problem $problem): ?AiInsight
    {
        $kpi = $problem->kpiDefinition;

        // Get related KPIs via relationship graph
        $relatedKpis = $this->getRelatedKpiData($kpi);

        // Recent values
        $recentValues = KpiValue::where('kpi_definition_id', $kpi->id)
            ->orderByDesc('recorded_at')
            ->limit(30)
            ->get(['value', 'recorded_at', 'status'])
            ->map(fn ($v) => [
                'date' => $v->recorded_at->format('Y-m-d'),
                'value' => (float) $v->value,
                'status' => $v->status,
            ]);

        $prompt = $this->buildPrompt('root_cause_analysis', [
            'problem_title' => $problem->title,
            'problem_description' => $problem->description,
            'problem_severity' => $problem->severity,
            'kpi_name_de' => $kpi->name_de,
            'kpi_name_en' => $kpi->name_en,
            'unit' => $kpi->unit,
            'target' => $kpi->target_value,
            'direction' => $kpi->direction,
            'recent_values' => $recentValues->toJson(),
            'related_kpis' => json_encode($relatedKpis),
            'department' => $kpi->department->name ?? 'N/A',
            'company' => $kpi->company->name ?? 'N/A',
        ]);

        $response = $this->chat($prompt);
        if (!$response) {
            return null;
        }

        $parsed = $this->parseJson($response);

        return AiInsight::create([
            'problem_id' => $problem->id,
            'kpi_definition_id' => $kpi->id,
            'department_id' => $problem->department_id,
            'company_id' => $kpi->company_id,
            'type' => 'root_cause',
            'title_de' => $parsed['title_de'] ?? 'KI-Ursachenanalyse',
            'title_en' => $parsed['title_en'] ?? 'AI Root Cause Analysis',
            'content_de' => $parsed['analysis_de'] ?? '',
            'content_en' => $parsed['analysis_en'] ?? '',
            'severity' => $problem->severity,
            'metadata' => [
                'probable_causes' => $parsed['probable_causes'] ?? [],
                'contributing_kpis' => $parsed['contributing_kpis'] ?? [],
                'confidence' => $parsed['confidence'] ?? 0.7,
                'recommended_investigation' => $parsed['recommended_investigation'] ?? [],
                'model' => $this->model,
            ],
        ]);
    }

    // ─── Private Helpers ────────────────────────────────────────────────────

    private function chat(string $prompt): ?string
    {
        try {
            $result = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a KPI analysis AI. Always respond with valid JSON. Be bilingual (German + English).'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

            return $result->choices[0]->message->content ?? null;
        } catch (\Exception $e) {
            Log::error('AI Service error: ' . $e->getMessage());
            return null;
        }
    }

    private function parseJson(string $content): ?array
    {
        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            Log::warning('AI JSON parse error: ' . $e->getMessage());
            return null;
        }
    }

    private function buildPrompt(string $type, array $data): string
    {
        return match ($type) {
            'anomaly_detection' => <<<PROMPT
Analyze the following KPI time-series data for anomalies.

KPI: {$data['kpi_name']} (Unit: {$data['unit']}, Direction: {$data['direction']})
Target: {$data['target']}, Warning: {$data['warning_threshold']}, Critical: {$data['critical_threshold']}

Values (JSON): {$data['values']}

Detect:
1. Sudden spikes or drops
2. Pattern changes (seasonality breaks)
3. Drift from normal operating range
4. Unusual clusters or outliers

Respond with JSON:
{
  "is_anomaly": true/false,
  "anomaly_type": "spike|drop|drift|pattern_change|outlier",
  "severity": "info|warning|critical",
  "confidence": 0.0-1.0,
  "affected_dates": ["2026-01-01"],
  "title_de": "German title",
  "title_en": "English title",
  "explanation_de": "Detailed German explanation (2-3 sentences)",
  "explanation_en": "Detailed English explanation (2-3 sentences)"
}
PROMPT,

            'kpi_recommendations' => <<<PROMPT
Recommend new KPIs for this company.

Company: {$data['company_name']}
Departments: {$data['departments']}
Existing KPIs: {$data['existing_kpis']}

Suggest 5-8 additional KPIs that would improve business monitoring. Focus on gaps in current coverage.

Respond with JSON:
{
  "recommendations": [
    {
      "name_de": "German name",
      "name_en": "English name",
      "category": "quality|production|sales|finance|hr|logistics|marketing",
      "unit": "%, Stück, EUR, etc.",
      "target_value": 95.0,
      "direction": "higher_better|lower_better",
      "department": "recommended department",
      "priority": "high|medium|low",
      "reason_de": "Why this KPI matters (German)",
      "reason_en": "Why this KPI matters (English)"
    }
  ]
}
PROMPT,

            'natural_language_insights' => <<<PROMPT
Generate a comprehensive performance analysis report for this KPI.

KPI: {$data['kpi_name_de']} / {$data['kpi_name_en']}
Unit: {$data['unit']}, Target: {$data['target']}, Direction: {$data['direction']}
Department: {$data['department']}, Company: {$data['company']}
Values: {$data['values']}
Recent Problems: {$data['recent_problems']}

Write a clear, data-driven analysis. Include specific numbers and percentages.

Respond with JSON:
{
  "title_de": "German report title",
  "title_en": "English report title",
  "report_de": "3-5 paragraph German analysis with specific data points, trends, and recommendations",
  "report_en": "3-5 paragraph English analysis with specific data points, trends, and recommendations",
  "trend": "improving|stable|declining|volatile",
  "performance_score": 0-100,
  "overall_status": "info|warning|critical",
  "key_findings": ["finding1", "finding2"],
  "recommendations": ["action1", "action2"]
}
PROMPT,

            'predictive_actions' => <<<PROMPT
Suggest corrective actions for this problem based on past similar problems and their successful resolutions.

Problem: {$data['problem_title']}
Description: {$data['problem_description']}
Severity: {$data['problem_severity']}
KPI: {$data['kpi_name_de']} / {$data['kpi_name_en']}
Department: {$data['department']}

Past actions for similar problems: {$data['past_actions']}

Suggest 3-5 concrete, actionable corrective measures. Prioritize based on past effectiveness.

Respond with JSON:
{
  "actions": [
    {
      "title_de": "German action title",
      "title_en": "English action title",
      "description_de": "Detailed German description",
      "description_en": "Detailed English description",
      "priority": "high|medium|low",
      "estimated_impact": "high|medium|low",
      "estimated_days": 7,
      "confidence": 0.8,
      "based_on_past": true
    }
  ]
}
PROMPT,

            'root_cause_analysis' => <<<PROMPT
Perform root cause analysis for this performance problem.

Problem: {$data['problem_title']}
Description: {$data['problem_description']}
Severity: {$data['problem_severity']}
KPI: {$data['kpi_name_de']} / {$data['kpi_name_en']} (Unit: {$data['unit']}, Target: {$data['target']})
Department: {$data['department']}, Company: {$data['company']}

Recent KPI Values: {$data['recent_values']}
Related KPIs: {$data['related_kpis']}

Analyze the data and identify probable root causes. Consider:
1. Upstream KPIs that may have caused the issue
2. Timing correlation between related KPI changes
3. Common industrial/business root causes
4. Systemic vs. one-time issues

Respond with JSON:
{
  "title_de": "German analysis title",
  "title_en": "English analysis title",
  "analysis_de": "Detailed German root cause analysis (3-5 paragraphs)",
  "analysis_en": "Detailed English root cause analysis (3-5 paragraphs)",
  "confidence": 0.0-1.0,
  "probable_causes": [
    {"cause_de": "German cause", "cause_en": "English cause", "likelihood": 0.8}
  ],
  "contributing_kpis": ["Related KPI names that may be contributing"],
  "recommended_investigation": ["Step 1", "Step 2"]
}
PROMPT,

            default => $data['prompt'] ?? '',
        };
    }

    private function buildChatContext(User $user): string
    {
        $context = "User: {$user->name} (Role: {$user->roles->first()?->name})\n";

        // Get user's accessible KPIs with latest values
        $kpis = KpiDefinition::whereHas('responsibleUsers', fn ($q) => $q->where('users.id', $user->id))
            ->with('latestValue')
            ->limit(20)
            ->get();

        if ($kpis->isEmpty()) {
            // If no assigned KPIs, get department/company KPIs
            $kpis = KpiDefinition::where('is_active', true)
                ->with('latestValue')
                ->limit(20)
                ->get();
        }

        $context .= "\nAccessible KPIs:\n";
        foreach ($kpis as $kpi) {
            $latest = $kpi->latestValue;
            $context .= "- {$kpi->name_de} ({$kpi->name_en}): ";
            if ($latest) {
                $context .= "Latest={$latest->value} {$kpi->unit}, Status={$latest->status}, Target={$kpi->target_value}";
            } else {
                $context .= "No data yet, Target={$kpi->target_value} {$kpi->unit}";
            }
            $context .= "\n";
        }

        // Recent problems
        $problems = Problem::where('status', 'open')
            ->latest()
            ->limit(5)
            ->get();

        if ($problems->isNotEmpty()) {
            $context .= "\nOpen Problems:\n";
            foreach ($problems as $p) {
                $context .= "- [{$p->severity}] {$p->title}\n";
            }
        }

        return $context;
    }

    private function getRelatedKpiData(KpiDefinition $kpi): array
    {
        $relationships = KpiRelationship::where('cause_kpi_id', $kpi->id)
            ->orWhere('effect_kpi_id', $kpi->id)
            ->with(['causeKpi', 'effectKpi'])
            ->limit(10)
            ->get();

        $relatedData = [];
        foreach ($relationships as $rel) {
            $relatedKpi = $rel->cause_kpi_id === $kpi->id ? $rel->effectKpi : $rel->causeKpi;
            if (!$relatedKpi) {
                continue;
            }

            $recentValues = KpiValue::where('kpi_definition_id', $relatedKpi->id)
                ->orderByDesc('recorded_at')
                ->limit(10)
                ->get(['value', 'recorded_at'])
                ->map(fn ($v) => ['date' => $v->recorded_at->format('Y-m-d'), 'value' => (float) $v->value]);

            $relatedData[] = [
                'kpi_name' => $relatedKpi->name_de . ' / ' . $relatedKpi->name_en,
                'relationship_type' => $rel->relationship_type,
                'strength' => $rel->strength,
                'direction' => $rel->cause_kpi_id === $kpi->id ? 'this_causes' : 'caused_by',
                'recent_values' => $recentValues->toArray(),
            ];
        }

        return $relatedData;
    }
}
