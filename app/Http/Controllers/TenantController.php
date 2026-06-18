<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')->get()->map(function ($tenant) {
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domains' => $tenant->domains->map(fn ($d) => [
                    'id' => $d->id,
                    'domain' => $d->domain,
                ]),
                'created_at' => $tenant->created_at?->toDateString(),
            ];
        });

        return Inertia::render('Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tenant = Tenant::create([
            'name' => $validated['name'],
        ]);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function update(Request $request, string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $tenant->update($validated);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroy(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->delete();

        return redirect()->back()->with('success', __('common.success'));
    }

    public function storeDomain(Request $request, string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
        ]);

        $tenant->domains()->create([
            'domain' => $validated['domain'],
        ]);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function destroyDomain(string $tenantId, Domain $domain)
    {
        $domain->delete();

        return redirect()->back()->with('success', __('common.success'));
    }
}
