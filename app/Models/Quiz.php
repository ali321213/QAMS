<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['subject_id', 'teacher_id', 'title', 'starts_at', 'deadline', 'published'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'deadline' => 'datetime',
            'published' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
