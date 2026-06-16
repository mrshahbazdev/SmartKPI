<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AiSettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Settings/AiSettings', [
            'hasApiKey' => !empty($user->openai_api_key),
            'maskedKey' => $this->maskKey($user->openai_api_key),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'openai_api_key' => ['nullable', 'string', 'max:200'],
        ]);

        $user = auth()->user();
        $user->update([
            'openai_api_key' => $validated['openai_api_key'] ?: null,
        ]);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function remove()
    {
        auth()->user()->update(['openai_api_key' => null]);

        return redirect()->back()->with('success', __('common.success'));
    }

    private function maskKey(?string $key): ?string
    {
        if (!$key) {
            return null;
        }

        $length = strlen($key);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($key, 0, 4) . str_repeat('*', $length - 8) . substr($key, -4);
    }
}
