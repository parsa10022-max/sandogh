<?php

namespace App\Models;

use App\Enums\AccountingStatus;
use App\Enums\PaymentBank;
use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $fillable = [
        'account_id',
        'account_transaction_id',
        'amount',
        'iban',
        'status',
        'description',
        'payment_bank',
        'payment_tracking_code',
        'paid_by',
        'paid_at',
        'accounting_status',
        'accounting_confirmed_by',
        'accounting_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WithdrawalStatus::class,
            'payment_bank' => PaymentBank::class,
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'accounting_status' => AccountingStatus::class,
            'accounting_confirmed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            AccountTransaction::class,
            'account_transaction_id'
        );
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'paid_by'
        );
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
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === WithdrawalStatus::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === WithdrawalStatus::PAID;
    }

    public function isCanceled(): bool
    {
        return $this->status === WithdrawalStatus::CANCELLED;
    }
}
