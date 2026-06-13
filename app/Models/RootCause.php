<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RootCause extends Model
{
    use HasFactory;

    protected $fillable = [
        'problem_id',
        'kpi_relationship_id',
        'description',
        'confidence',
        'source',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }

    public function kpiRelationship(): BelongsTo
    {
        return $this->belongsTo(KpiRelationship::class);
    }
}
