<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'theme',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function company()
    {
        return $this->department?->company();
    }

    public function assignedProblems(): HasMany
    {
        return $this->hasMany(Problem::class, 'assigned_to');
    }

    public function assignedActions(): HasMany
    {
        return $this->hasMany(Action::class, 'assigned_to');
    }

    public function createdActions(): HasMany
    {
        return $this->hasMany(Action::class, 'created_by');
    }

    public function ownedKpis()
    {
        return $this->belongsToMany(KpiDefinition::class, 'kpi_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function alertRules()
    {
        return $this->hasMany(AlertRule::class, 'notify_user_id');
    }
}
