<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'app_name', 'logo_path', 'primary_color',
        'secondary_color', 'favicon_path', 'onboarding_completed', 'onboarding_step',
    ];

    protected $casts = [
        'onboarding_completed' => 'boolean',
    ];
}
