<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInsight extends Model
{
    protected $fillable = [
        'kpi_definition_id',
        'company_id',
        'department_id',
        'problem_id',
        'user_id',
        'type',
        'title_de',
        'title_en',
        'content_de',
        'content_en',
        'metadata',
        'severity',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function getTitleAttribute(): string
    {
        return app()->getLocale() === 'de' ? $this->title_de : $this->title_en;
    }

    public function getContentAttribute(): string
    {
        return app()->getLocale() === 'de' ? $this->content_de : $this->content_en;
    }

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
