<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class KpiDefinition extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name_de', 'name_en', 'target_value', 'warning_threshold', 'critical_threshold', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('kpi');
    }

    protected $fillable = [
        'department_id',
        'company_id',
        'name_de',
        'name_en',
        'description_de',
        'description_en',
        'formula',
        'unit',
        'target_value',
        'warning_threshold',
        'critical_threshold',
        'frequency',
        'direction',
        'category',
        'is_template',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:4',
        'warning_threshold' => 'decimal:4',
        'critical_threshold' => 'decimal:4',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'de' ? $this->name_de : $this->name_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'de' ? $this->description_de : $this->description_en;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(KpiValue::class);
    }

    public function latestValue()
    {
        return $this->hasOne(KpiValue::class)->latestOfMany('recorded_at');
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }

    public function causeRelationships(): HasMany
    {
        return $this->hasMany(KpiRelationship::class, 'cause_kpi_id');
    }

    public function effectRelationships(): HasMany
    {
        return $this->hasMany(KpiRelationship::class, 'effect_kpi_id');
    }

    public function responsibleUsers()
    {
        return $this->belongsToMany(User::class, 'kpi_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }
}
