<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\KpiDefinition;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function index()
    {
        $settings = TenantSetting::first();
        $step = $settings?->onboarding_step ?? 0;

        if ($settings?->onboarding_completed) {
            return redirect('/dashboard');
        }

        $companies = Company::with('departments')->get();
        $templates = KpiDefinition::where('is_template', true)->get();

        return Inertia::render('Onboarding/Wizard', [
            'currentStep' => $step,
            'companies' => $companies,
            'templates' => $templates,
        ]);
    }

    public function updateStep(Request $request)
    {
        $validated = $request->validate([
            'step' => ['required', 'integer', 'between:0,5'],
        ]);

        $settings = TenantSetting::firstOrCreate(
            ['tenant_id' => 'default'],
            ['onboarding_step' => 0]
        );

        $settings->update(['onboarding_step' => $validated['step']]);

        return response()->json(['step' => $validated['step']]);
    }

    public function saveLanguage(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:de,en'],
        ]);

        Auth::user()->update(['locale' => $validated['locale']]);
        $this->advanceStep(1);

        return redirect()->back();
    }

    public function saveStructure(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string'],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => ['string', 'max:255'],
        ]);

        $company = Company::create([
            'tenant_id' => 'default',
            'name' => $validated['company_name'],
            'industry' => $validated['industry'] ?? null,
            'is_active' => true,
        ]);

        foreach ($validated['departments'] as $deptName) {
            Department::create([
                'company_id' => $company->id,
                'name' => $deptName,
            ]);
        }

        $this->advanceStep(2);

        return redirect()->back();
    }

    public function saveKpis(Request $request)
    {
        $validated = $request->validate([
            'template_ids' => ['required', 'array'],
            'template_ids.*' => ['exists:kpi_definitions,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        foreach ($validated['template_ids'] as $templateId) {
            $template = KpiDefinition::find($templateId);
            if (!$template) continue;

            KpiDefinition::create([
                'department_id' => $validated['department_id'],
                'company_id' => $validated['company_id'],
                'name_de' => $template->name_de,
                'name_en' => $template->name_en,
                'description_de' => $template->description_de,
                'description_en' => $template->description_en,
                'formula' => $template->formula,
                'unit' => $template->unit,
                'target_value' => $template->target_value,
                'warning_threshold' => $template->warning_threshold,
                'critical_threshold' => $template->critical_threshold,
                'frequency' => $template->frequency,
                'direction' => $template->direction,
                'category' => $template->category,
                'is_template' => false,
                'is_active' => true,
            ]);
        }

        $this->advanceStep(3);

        return redirect()->back();
    }

    public function saveInvitations(Request $request)
    {
        $validated = $request->validate([
            'invitations' => ['required', 'array'],
            'invitations.*.email' => ['required', 'email'],
            'invitations.*.name' => ['required', 'string'],
            'invitations.*.department_id' => ['required', 'exists:departments,id'],
        ]);

        foreach ($validated['invitations'] as $inv) {
            User::create([
                'name' => $inv['name'],
                'email' => $inv['email'],
                'password' => Hash::make('changeme123'),
                'department_id' => $inv['department_id'],
                'locale' => Auth::user()->locale ?? 'de',
            ]);
        }

        $this->advanceStep(4);

        return redirect()->back();
    }

    public function complete()
    {
        $settings = TenantSetting::firstOrCreate(
            ['tenant_id' => 'default'],
            ['onboarding_step' => 0]
        );

        $settings->update([
            'onboarding_completed' => true,
            'onboarding_step' => 5,
        ]);

        return redirect('/dashboard');
    }

    private function advanceStep(int $step): void
    {
        $settings = TenantSetting::firstOrCreate(
            ['tenant_id' => 'default'],
            ['onboarding_step' => 0]
        );

        if ($settings->onboarding_step < $step) {
            $settings->update(['onboarding_step' => $step]);
        }
    }
}
