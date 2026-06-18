<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'kpi_definition_id',
        'name',
        'type',
        'conditions',
        'severity',
        'is_active',
        'auto_create_problem',
        'notify_user_id',
        'escalation_hours',
        'escalate_to_user_id',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'auto_create_problem' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function notifyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notify_user_id');
    }

    public function escalateToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalate_to_user_id');
    }
}
