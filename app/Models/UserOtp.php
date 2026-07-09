<?php

namespace App\Models;

use App\Enums\UserOtpStatus;
use App\Enums\UserOtpType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mobile',
        'code',
        'type',
        'status',
        'attempts',
        'expires_at',
        'verified_at',
        'cancelled_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'type' => UserOtpType::class,
            'status' => UserOtpStatus::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', UserOtpStatus::PENDING);
    }

    public function scopeLogin(Builder $query): Builder
    {
        return $query->where('type', UserOtpType::LOGIN);
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query
            ->where('status', UserOtpStatus::PENDING)
            ->where('expires_at', '>', now());
    }
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->whereNull('cancelled_at');
    }
    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at->isPast();
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verified_at !== null;
    }
}
