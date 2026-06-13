<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'cause_kpi_id',
        'effect_kpi_id',
        'weight',
        'confidence',
        'lag_days',
        'description',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'confidence' => 'decimal:2',
    ];

    public function causeKpi(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class, 'cause_kpi_id');
    }

    public function effectKpi(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class, 'effect_kpi_id');
    }
}
