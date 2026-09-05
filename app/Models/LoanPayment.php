<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'loan_id',
        'installment_id',
        'user_id',
        'amount',
        'tracking_code',
        'gateway',
        'bank_transaction_id',
        'bank_reference_number',
        'paid_at',

        'accounting_status',
        'accounting_confirmed_by',
        'accounting_confirmed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'amount'   => 'integer',
            'gateway'  => PaymentGateway::class,
            'paid_at'  => 'datetime',
            'accounting_status' =>
                \App\Enums\AccountingStatus::class,

            'accounting_confirmed_at' =>
                'datetime',
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * وام
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * قسط
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * پرداخت کننده
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accountingConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'accounting_confirmed_by'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * پرداخت‌های یک وام
     */
    public function scopeByLoan(Builder $query, int $loanId): Builder
    {
        return $query->where('loan_id', $loanId);
    }

    /**
     * پرداخت‌های یک قسط
     */
    public function scopeByInstallment(Builder $query, int $installmentId): Builder
    {
        return $query->where('installment_id', $installmentId);
    }

    /**
     * پرداخت‌های یک کاربر
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * جستجو با کد رهگیری
     */
    public function scopeTrackingCode(Builder $query, string $trackingCode): Builder
    {
        return $query->where('tracking_code', $trackingCode);
    }

    /**
     * پرداخت‌های امروز
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('paid_at', today());
    }

    /**
     * بازه زمانی
     */
    public function scopeBetweenDates(
        Builder $query,
        string $from,
        string $to
    ): Builder {
        return $query->whereBetween('paid_at', [$from, $to]);
    }

    /**
     * آخرین پرداخت‌ها
     */
    public function scopeLatestPaid(Builder $query): Builder
    {
        return $query->latest('paid_at');
    }

    public function getPaidAtJalaliAttribute(): ?string
    {
        if (! $this->paid_at) {
            return null;
        }

        return app(\App\Services\Date\JalaliDateService::class)
            ->toJalali($this->paid_at);
    }
}
