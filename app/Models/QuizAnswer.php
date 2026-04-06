<?php

namespace App\Models;

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
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBankItem::class, 'question_bank_item_id');
    }
}
