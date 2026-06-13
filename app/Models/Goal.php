<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'kpi_definition_id',
        'title',
        'description',
        'target_value',
        'current_value',
        'start_date',
        'end_date',
        'status',
        'progress',
        'assigned_to',
    ];

    protected $casts = [
        'target_value' => 'decimal:4',
        'current_value' => 'decimal:4',
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
