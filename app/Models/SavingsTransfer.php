<?php

namespace App\Models;

use App\Enums\PaymentGateway;
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

    ];


    protected function casts(): array
    {
        return [

            'amount' => 'integer',

            'gateway' => PaymentGateway::class,

            'paid_at' => 'datetime',

        ];
    }



    public function sender(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sender_user_id'
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
}
