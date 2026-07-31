<?php

namespace App\Models;

use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PaymentMethod;


class AccountTransaction extends Model
{
    protected $fillable = [
        'account_id',
        'transaction_no',
        'transaction_type',
        'transaction_source',
        'amount',
        'balance_before',
        'balance_after',
        'payment_method',
        'transaction_date',
        'created_by',
        'description',
    ];


    protected function casts(): array
    {
        return [
            'transaction_type' => TransactionType::class,
            'transaction_source' => TransactionSource::class,

            'amount' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'payment_method' => PaymentMethod::class,
            'transaction_date' => 'date',
        ];
    }


    /**
     * حساب مربوط به تراکنش
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function withdrawal()
    {
        return $this->hasOne(Withdrawal::class);
    }


    /**
     * کاربر ثبت کننده
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
