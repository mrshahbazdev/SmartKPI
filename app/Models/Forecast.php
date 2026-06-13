<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Forecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_definition_id',
        'forecast_date',
        'predicted_value',
        'lower_bound',
        'upper_bound',
        'confidence',
        'horizon',
        'method',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'predicted_value' => 'decimal:4',
        'lower_bound' => 'decimal:4',
        'upper_bound' => 'decimal:4',
        'confidence' => 'decimal:2',
    ];

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }
}
