<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Inertia\Inertia;

class PricingController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Pricing/Index', [
            'plans' => $plans,
        ]);
    }
}
