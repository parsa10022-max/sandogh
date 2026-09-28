<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentIntent extends Model
{
    protected $fillable = [
        'payment_type',
        'reference_id',
        'payer_user_id',
        'amount',
        'tracking_code',
        'gateway',
        'gateway_token',
        'status',
        'gateway_transaction_id',
        'gateway_reference_number',
        'expires_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'payer_user_id' => 'integer',
            'amount' => 'integer',
            'gateway' => PaymentGateway::class,
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'payer_user_id'
        );
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeRedirected(Builder $query): Builder
    {
        return $query->where('status', 'redirected');
    }

    public function scopeVerifying(Builder $query): Builder
    {
        return $query->where('status', 'verifying');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    public function scopeForReference(
        Builder $query,
        string $paymentType,
        int $referenceId
    ): Builder {
        return $query
            ->where('payment_type', $paymentType)
            ->where('reference_id', $referenceId);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRedirected(): bool
    {
        return $this->status === 'redirected';
    }

    public function isVerifying(): bool
    {
        return $this->status === 'verifying';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isExpiredByTime(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}
