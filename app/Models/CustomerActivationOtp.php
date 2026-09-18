<?php

namespace App\Models;

use App\Enums\CustomerActivationOtpStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivationOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'mobile',
        'code',
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
            'status' => CustomerActivationOtpStatus::class,
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            CustomerActivationOtpStatus::PENDING
        );
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query
            ->where(
                'status',
                CustomerActivationOtpStatus::PENDING
            )
            ->where('expires_at', '>', now());
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->whereNull('cancelled_at');
    }

    public function scopeForCustomer(
        Builder $query,
        int $customerId
    ): Builder {
        return $query->where('customer_id', $customerId);
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
