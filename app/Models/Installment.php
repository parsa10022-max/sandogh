<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use App\Models\Concerns\HasJalaliDates;
use App\Services\Date\JalaliDateService;
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

    public function payment()
    {
        return $this->hasOne(
            LoanPayment::class,
            'installment_id'
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
     * آیا پرداخت شده است؟
     */
    public function getIsPaidAttribute(): bool
    {
        return ! is_null($this->paid_at);
    }

    /**
     * شماره قسط
     */
    public function getNumberAttribute(): int
    {
        return $this->installment_number;
    }

    /**
     * تاریخ سررسید شمسی
     */
    public function getDueDateJalaliAttribute(): string
    {
        return app(JalaliDateService::class)
            ->toJalali($this->due_date);
    }

    /**
     * تاریخ پرداخت شمسی
     */
    public function getPaidAtJalaliAttribute(): ?string
    {
        if (! $this->paid_at) {
            return null;
        }

        return app(JalaliDateService::class)
            ->toJalali($this->paid_at);
    }

    /**
     * تعداد روزهای تأخیر
     */
    public function getOverdueDaysAttribute(): int
    {
        if ($this->status === InstallmentStatus::PAID) {
            return 0;
        }

        $today = now()->startOfDay();
        $due = $this->due_date->startOfDay();

        if ($due->gte($today)) {
            return 0;
        }

        return $due->diffInDays($today);
    }
}
