<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name_de', 'name_en', 'description_de', 'description_en',
        'price_monthly', 'price_yearly', 'currency',
        'max_companies', 'max_departments', 'max_kpis', 'max_users',
        'has_api_access', 'has_forecasting', 'has_cross_company', 'has_white_label',
        'features', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'has_api_access' => 'boolean',
        'has_forecasting' => 'boolean',
        'has_cross_company' => 'boolean',
        'has_white_label' => 'boolean',
    ];
}
