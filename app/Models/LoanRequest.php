<?php

namespace App\Models;

use App\Enums\LoanRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRequest extends Model
{
    protected $fillable = [

        'customer_id',

        'requested_amount',

        'approved_amount',

        'description',

        'status',

        'review_note',

        'loan_id',

        'next_review_date',

        'loan_type_id',

        'approved_installment_count',

        'approved_installment_interval',

        'reviewed_by',

        'reviewed_at',

    ];


    protected $casts = [

        'status' => LoanRequestStatus::class,

        'reviewed_at' => 'datetime',

        'next_review_date' => 'date',

        'requested_amount' => 'integer',

        'approved_amount' => 'integer',

        'approved_installment_count' => 'integer',

        'approved_installment_interval' => 'integer',

    ];


    /**
     * عضو
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    /**
     * بررسی کننده
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }


    /**
     * وام ایجاد شده از این درخواست
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(
            Loan::class
        );
    }


    /**
     * نوع وام
     */
    public function loanType(): BelongsTo
    {
        return $this->belongsTo(
            LoanType::class
        );
    }
}
