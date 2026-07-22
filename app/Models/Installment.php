<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use App\Models\Concerns\HasJalaliDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Installment extends Model
{

    use HasJalaliDates;

    protected $fillable = [

        'loan_id',

        'installment_number',

        'amount',

        'due_date',

        'status',

        'paid_at',

        'description',

        'created_by',

        'updated_by',

    ];

    protected function casts(): array
    {
        return [

            'amount' => 'integer',

            'status' => InstallmentStatus::class,

            'due_date' => 'date',

            'paid_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * مبلغ با جداکننده
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount);
    }

    /**
     * وضعیت پرداخت
     */
    public function getIsPaidAttribute(): bool
    {
        return ! is_null($this->paid_at);
    }

    /**
     * تاریخ سررسید شمسی
     */
    public function getDueDateJalaliAttribute(): string
    {
        return $this->toJalali($this->due_date);
    }

    /**
     * شماره قسط (برای نمایش)
     */
    public function getNumberAttribute(): int
    {
        return $this->installment_number;
    }
}
