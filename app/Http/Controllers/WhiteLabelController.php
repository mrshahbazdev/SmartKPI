<?php

namespace App\Http\Controllers;

use App\Models\TenantSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhiteLabelController extends Controller
{
    public function index()
    {
        $settings = TenantSetting::firstOrCreate(
            ['tenant_id' => 'default'],
            ['app_name' => 'SmartKPI', 'primary_color' => '#4F46E5', 'secondary_color' => '#7C3AED']
        );

        return Inertia::render('Settings/WhiteLabel', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:100'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $settings = TenantSetting::firstOrCreate(
            ['tenant_id' => 'default'],
            ['onboarding_step' => 0]
        );

        $settings->update($validated);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            $settings->update(['logo_path' => $path]);
        }

        return redirect()->back()->with('success', __('common.success'));
    }
}
