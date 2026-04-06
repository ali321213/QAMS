<?php

namespace App\Models;

<<<<<<< Updated upstream
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'selected_option',
        'is_correct',
        'earned_marks',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt()
=======
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $fillable = ['quiz_attempt_id', 'question_bank_item_id', 'selected_option', 'is_correct'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function attempt(): BelongsTo
>>>>>>> Stashed changes
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

<<<<<<< Updated upstream
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}

=======
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBankItem::class, 'question_bank_item_id');
    }
}
>>>>>>> Stashed changes
