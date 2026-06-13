<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyRiskScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'score_date',
        'risk_score',
        'kpi_health',
        'open_problems',
        'critical_problems',
        'overdue_actions',
        'breakdown',
    ];

    protected $casts = [
        'score_date' => 'date',
        'risk_score' => 'decimal:2',
        'kpi_health' => 'decimal:2',
        'breakdown' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
