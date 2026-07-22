<?php

namespace App\Models;

use App\Enums\InstallmentInterval;
use App\Enums\LoanStatus;
use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasJalaliDates;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use HasFactory, SoftDeletes ,HasJalaliDates;

    protected $fillable = [
        'customer_id',
        'loan_type_id',
        'loan_number',

        'loan_amount',
        'installment_amount',

        'installment_count',
        'installment_interval',

        'start_date',
        'first_due_date',
        'last_due_date',

        'status',
        'description',

        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'installment_interval' => InstallmentInterval::class,

            'loan_amount' => 'integer',
            'installment_amount' =>'integer',

            'start_date' => 'date',
            'first_due_date' => 'date',
            'last_due_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function installments(): HasMany
    {
        return $this->hasMany(
            Installment::class
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'status',
            LoanStatus::ACTIVE->value
        );
    }

    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        // حذف خط تیره از جستجو
        $search = str_replace('-', '', $search);

        return $query
            ->where(function ($q) use ($search) {

                // جستجو شماره وام (پیشوند + شماره وام)
                $q->whereHas('loanType', function ($loanTypeQuery) use ($search) {

                    $loanTypeQuery->whereRaw(
                        "CONCAT(prefix, loans.loan_number) LIKE ?",
                        ["%{$search}%"]
                    );

                })

                    // جستجو اطلاعات مشتری
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {

                        $customerQuery
                            ->where('customer_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");

                    });

            });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * شماره کامل وام
     */
    public function getFullLoanNumberAttribute(): string
    {
        return "{$this->loanType->prefix}-{$this->loan_number}";
    }

    /**
     * مبلغ وام با جداکننده
     */
    public function getFormattedLoanAmountAttribute(): string
    {
        return number_format($this->loan_amount);
    }

    /**
     * مبلغ هر قسط
     */
    public function getFormattedInstallmentAmountAttribute(): string
    {
        return number_format($this->installment_amount);
    }
    public function getStartDateJalaliAttribute(): string
    {
        return $this->toJalali($this->start_date);
    }

    public function getFirstDueDateJalaliAttribute(): string
    {
        return $this->toJalali($this->first_due_date);
    }

    public function getLastDueDateJalaliAttribute(): string
    {
        return $this->toJalali($this->last_due_date);
    }

    public function getCreatedAtJalaliAttribute(): string
    {
        return $this->toJalali($this->created_at, 'Y/m/d H:i');
    }

    public function getUpdatedAtJalaliAttribute(): string
    {
        return $this->toJalali($this->updated_at, 'Y/m/d H:i');
    }
}
