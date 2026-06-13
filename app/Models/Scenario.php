<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'description',
        'assumptions',
        'projected_outcomes',
    ];

    protected $casts = [
        'assumptions' => 'array',
        'projected_outcomes' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
