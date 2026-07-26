<?php

namespace App\Models;

use App\Enums\GuaranteeType;
use App\Enums\GuarantorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class LoanGuarantor extends Model
{
    protected $fillable = [

        'loan_id',

        'guarantor_order',

        'guarantor_type',

        'customer_id',

        'first_name',

        'last_name',

        'national_code',

        'mobile',

        'guarantee_type',

    ];

    protected $casts = [

        'guarantor_type' => GuarantorType::class,

        'guarantee_type' => GuaranteeType::class,

    ];

    protected function casts(): array
    {
        return [
            'guarantor_type' => GuarantorType::class,
            'guarantee_type' => GuaranteeType::class,
        ];
    }

    /**
     * وام مربوطه
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * مشتری عضو صندوق
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * نام کامل ضامن
     */
    public function getFullNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->full_name;
        }

        return trim(
            ($this->first_name ?? '') . ' ' . ($this->last_name ?? '')
        );
    }

    /**
     * عضو صندوق است؟
     */
    public function getIsCustomerAttribute(): bool
    {
        return ! is_null($this->customer_id);
    }
}
