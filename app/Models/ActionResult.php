<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_id',
        'kpi_value_before',
        'kpi_value_after',
        'notes',
        'effectiveness_score',
    ];

    protected $casts = [
        'kpi_value_before' => 'decimal:4',
        'kpi_value_after' => 'decimal:4',
        'effectiveness_score' => 'decimal:2',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }
}
