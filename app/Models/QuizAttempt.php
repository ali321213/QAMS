<?php

namespace App\Models;

<<<<<<< Updated upstream
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'started_at',
        'submitted_at',
        'total_score',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function quiz()
=======
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = ['quiz_id', 'student_id', 'score', 'total_questions', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function quiz(): BelongsTo
>>>>>>> Stashed changes
    {
        return $this->belongsTo(Quiz::class);
    }

<<<<<<< Updated upstream
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
=======
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
>>>>>>> Stashed changes
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
<<<<<<< Updated upstream

=======
>>>>>>> Stashed changes
