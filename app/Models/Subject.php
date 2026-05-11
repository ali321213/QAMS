<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['school_class_id', 'name'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subject_teacher');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_subject');
    }

    public function questionBankItems(): HasMany
    {
        return $this->hasMany(QuestionBankItem::class);
    }
}
