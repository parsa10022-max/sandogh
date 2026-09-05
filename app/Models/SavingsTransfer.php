<?php

namespace App\Models;

use App\Enums\AccountingStatus;
use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransfer extends Model
{
    protected $fillable = [

        'sender_user_id',

        'receiver_customer_id',

        'account_id',

        'amount',

        'tracking_code',

        'gateway',

        'bank_transaction_id',

        'bank_reference_number',

        'status',

        'paid_at',

        'accounting_status',
        'accounting_confirmed_by',
        'accounting_confirmed_at',

    ];

    protected function casts(): array
    {
        return [

            'amount' => 'integer',

            'gateway' => PaymentGateway::class,

            'paid_at' => 'datetime',

            'accounting_status' =>
                AccountingStatus::class,

            'accounting_confirmed_at' =>
                'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function sender(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sender_user_id'
        );
    }

    public function accountingConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'accounting_confirmed_by'
        );
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'receiver_customer_id'
        );
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            Account::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accounting Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * واریزهایی که پرداخت شده‌اند
     * ولی هنوز در حسابداری تأیید نشده‌اند.
     */
    public function scopePendingAccounting(Builder $query): Builder
    {
        return $query
            ->where('status', 'paid')
            ->where(
                'accounting_status',
                AccountingStatus::PENDING
            );
    }

    /**
     * واریز به حساب پس‌انداز خود
     *
     * فرستنده و صاحب حساب مقصد یک نفر هستند.
     */
    public function scopeOwn(Builder $query): Builder
    {
        return $query->whereHas(
            'receiver.user',
            function (Builder $userQuery) {
                $userQuery->whereColumn(
                    'users.id',
                    'savings_transfers.sender_user_id'
                );
            }
        );
    }

    /**
     * واریز به حساب پس‌انداز شخص دیگر
     *
     * فرستنده و صاحب حساب مقصد متفاوت هستند.
     */
    public function scopeOther(Builder $query): Builder
    {
        return $query->whereDoesntHave(
            'receiver.user',
            function (Builder $userQuery) {
                $userQuery->whereColumn(
                    'users.id',
                    'savings_transfers.sender_user_id'
                );
            }
        );
    }
}
