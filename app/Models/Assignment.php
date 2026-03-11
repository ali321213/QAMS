<?php

namespace App\Models;

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
    {
        return $this->belongsTo(Subject::class);
    }

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

