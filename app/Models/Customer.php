<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'customer_code',
        'first_name',
        'last_name',
        'father_name',
        'national_code',
        'iban',
        'mobile',
        'mobile_second',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
        ];
    }

    /**
     * حساب کاربری عضو
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'customer_id');
    }

    /**
     * وام‌هایی که مشتری ضامن آنهاست
     */
    public function guarantorLoans(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    /**
     * حساب‌های عضو
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * OTPهای فعال‌سازی
     */
    public function activationOtps(): HasMany
    {
        return $this->hasMany(CustomerActivationOtp::class);
    }

    /**
     * وام‌های مشتری
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * مشتریان فعال
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'status',
            CustomerStatus::ACTIVE
        );
    }

    /**
     * جستجوی مشتری
     */
    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (filled($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where(
                    'customer_code',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'first_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'last_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'national_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'mobile',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        return $query;
    }

    /**
     * نام کامل
     */
    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' . $this->last_name
        );
    }

    /**
     * نام نمایشی
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->customer_code} - {$this->full_name}";
    }
}
