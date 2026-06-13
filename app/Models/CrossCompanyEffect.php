<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrossCompanyEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_problem_id',
        'source_company_id',
        'affected_company_id',
        'affected_kpi_id',
        'impact_type',
        'impact_score',
        'description',
        'status',
    ];

    protected $casts = [
        'impact_score' => 'decimal:2',
    ];

    public function sourceProblem(): BelongsTo
    {
        return $this->belongsTo(Problem::class, 'source_problem_id');
    }

    public function sourceCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'source_company_id');
    }

    public function affectedCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'affected_company_id');
    }

    public function affectedKpi(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class, 'affected_kpi_id');
    }
}
