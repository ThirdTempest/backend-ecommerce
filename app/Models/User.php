<?php

namespace App\Models;

// 1. Add this import line
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // 2. Add HasApiTokens to this list
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp_code',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_type',
        'face_descriptor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'face_descriptor',
    ];

    protected $appends = [
        'has_totp',
        'has_face',
    ];

    public function getHasTotpAttribute()
    {
        return !is_null($this->two_factor_secret);
    }

    public function getHasFaceAttribute()
    {
        return !is_null($this->face_descriptor);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
