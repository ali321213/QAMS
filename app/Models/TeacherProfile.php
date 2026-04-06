<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $fillable = ['user_id', 'job_history', 'education'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
