<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = Webhook::orderByDesc('created_at')->paginate(20);

        return Inertia::render('Webhooks/Index', [
            'webhooks' => $webhooks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
            'event' => ['required', 'string'],
            'secret' => ['nullable', 'string'],
        ]);

        Webhook::create($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'url' => ['sometimes', 'url'],
            'event' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $webhook->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();
        return redirect()->back()->with('success', __('common.success'));
    }

    public function test(Webhook $webhook)
    {
        try {
            $payload = [
                'event' => 'test',
                'timestamp' => now()->toISOString(),
                'data' => ['message' => 'SmartKPI webhook test'],
            ];

            $headers = ['Content-Type' => 'application/json'];
            if ($webhook->secret) {
                $headers['X-Webhook-Secret'] = $webhook->secret;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($webhook->url, $payload);

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fire webhooks for an event.
     */
    public static function fire(string $event, array $data): void
    {
        $webhooks = Webhook::where('event', $event)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            try {
                $payload = [
                    'event' => $event,
                    'timestamp' => now()->toISOString(),
                    'data' => $data,
                ];

                $headers = ['Content-Type' => 'application/json'];
                if ($webhook->secret) {
                    $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);
                    $headers['X-Webhook-Signature'] = $signature;
                }

                Http::withHeaders($headers)
                    ->timeout(10)
                    ->post($webhook->url, $payload);

                $webhook->update([
                    'last_triggered_at' => now(),
                    'failure_count' => 0,
                ]);
            } catch (\Exception $e) {
                $webhook->increment('failure_count');
                if ($webhook->failure_count >= 10) {
                    $webhook->update(['is_active' => false]);
                }
            }
        }
    }
}
