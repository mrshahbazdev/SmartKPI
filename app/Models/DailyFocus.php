<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyFocus extends Model
{
    use HasFactory;

    protected $table = 'daily_focus';

    protected $fillable = [
        'department_id',
        'action_id',
        'problem_id',
        'title',
        'description',
        'focus_date',
        'priority',
    ];

    protected $casts = [
        'focus_date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
