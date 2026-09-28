<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationPayment extends Model
{
    protected $fillable = [
        'customer_id',
        'donor_name',
        'donor_mobile',
        'account_id',
        'amount',
        'tracking_code',
        'gateway',
        'status',
        'bank_transaction_id',
        'bank_reference_number',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => 'integer',
            'gateway' => PaymentGateway::class,
            'paid_at' => 'datetime',
        ];
    }

    /**
     * حساب مقصد
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * مشتری
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
