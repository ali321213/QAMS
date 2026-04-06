<?php

namespace App\Models;

<<<<<<< Updated upstream
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'teacher_id',
        'starts_at',
        'ends_at',
        'is_published',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function subject()
=======
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
>>>>>>> Stashed changes
    {
        return $this->belongsTo(Subject::class);
    }

<<<<<<< Updated upstream
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
=======
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attempts(): HasMany
>>>>>>> Stashed changes
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
<<<<<<< Updated upstream

=======
>>>>>>> Stashed changes
