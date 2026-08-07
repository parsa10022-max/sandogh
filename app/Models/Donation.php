<?php

namespace App\Models;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = [

        'donation_type_id',

        'account_id',

        'customer_id',

        'amount',

        'payment_method',

        'status',

        'tracking_code',

        'reference_number',

        'paid_at',

        'description',

        'created_by',

    ];

    protected function casts(): array
    {
        return [

            'payment_method' => PaymentMethod::class,

            'status' => DonationStatus::class,

            'paid_at' => 'datetime',

        ];
    }

    public function donationType(): BelongsTo
    {
        return $this->belongsTo(DonationType::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
