<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tenant;

class OrganizationController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['companies.departments'])->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->data['name'] ?? $tenant->id,
                    'companies' => $tenant->companies->map(function ($company) {
                        return [
                            'id' => $company->id,
                            'name' => $company->name,
                            'description' => $company->description,
                            'industry' => $company->industry,
                            'is_active' => $company->is_active,
                            'departments' => $company->departments->map(function ($dept) {
                                return [
                                    'id' => $dept->id,
                                    'name' => $dept->name,
                                    'description' => $dept->description,
                                    'is_active' => $dept->is_active,
                                    'users_count' => $dept->users()->count(),
                                ];
                            }),
                        ];
                    }),
                ];
            });

        return Inertia::render('Organizations/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function storeCompany(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'industry' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        Company::create($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function updateCompany(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'industry' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroyCompany(Company $company)
    {
        $company->delete();
        return redirect()->back()->with('success', __('common.success'));
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'parent_id' => ['nullable', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'industry_type' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:50'],
        ]);

        Department::create($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'industry_type' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:50'],
        ]);

        $department->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroyDepartment(Department $department)
    {
        $department->delete();
        return redirect()->back()->with('success', __('common.success'));
    }
}
