<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    
    // The table associated with the model.
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'password',
        'role',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Get the name of the unique identifier for the user.
    public function username(): string
    {
        return 'user_name';
    }

    // Check if user account is active (not blocked).
    public function isActive(): bool
    {
        return $this->active === '1';
    }

    // Check if user is admin.
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
