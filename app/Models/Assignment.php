<?php

namespace App\Models;

<<<<<<< Updated upstream
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'attachment_path',
        'subject_id',
        'teacher_id',
        'assigned_at',
        'deadline_at',
        'extended_deadline_at',
        'is_closed',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'deadline_at' => 'datetime',
        'extended_deadline_at' => 'datetime',
        'is_closed' => 'boolean',
    ];

    public function subject()
=======
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['subject_id', 'teacher_id', 'title', 'description', 'deadline', 'published'];

    protected function casts(): array
    {
        return [
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

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function effectiveDeadline()
    {
        return $this->extended_deadline_at ?? $this->deadline_at;
    }
}

=======
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
>>>>>>> Stashed changes
