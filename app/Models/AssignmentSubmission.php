<?php

namespace App\Models;

<<<<<<< Updated upstream
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;

=======
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
>>>>>>> Stashed changes
    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'submitted_at',
        'marks',
<<<<<<< Updated upstream
        'feedback',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function assignment()
=======
        'graded_by',
        'status',
        'feedback',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function assignment(): BelongsTo
>>>>>>> Stashed changes
    {
        return $this->belongsTo(Assignment::class);
    }

<<<<<<< Updated upstream
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

=======
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public static function applyAutoZeroMarks(): int
    {
        $now = Carbon::now();
        $submissions = static::query()
            ->where('status', 'pending')
            ->where(function (Builder $query) {
                $query->whereNull('submitted_at')->orWhereColumn('submitted_at', '>', 'assignments.deadline');
            })
            ->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
            ->where('assignments.deadline', '<', $now)
            ->select('assignment_submissions.id')
            ->get();

        if ($submissions->isEmpty()) {
            return 0;
        }

        return static::whereIn('id', $submissions->pluck('id'))
            ->update(['marks' => 0, 'status' => 'auto_zero']);
    }
}
>>>>>>> Stashed changes
