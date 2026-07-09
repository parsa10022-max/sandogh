<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;

use Database\Factories\UserFactory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'customer_id',
        'username',
        'mobile',
        'email',
        'password',
        'role',
        'status',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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

            'status' => UserStatus::class,

            'role' => UserRole::class,

            'mobile_verified_at' => 'datetime',

            'email_verified_at' => 'datetime',

            'last_login_at' => 'datetime',

        ];
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(UserOtp::class);
    }



    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::ACTIVE);
    }

    public function scopeOperator(Builder $query): Builder
    {
        return $query->where('role', UserRole::OPERATOR);
    }

}
