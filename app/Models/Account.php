<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AccountTransaction;



class Account extends Model
{
    protected $fillable = [
        'customer_id',
        'account_number',
        'account_type',
        'balance',
        'status',
        'name',
        'opened_date',
        'closed_date',
    ];

    protected function casts(): array
    {
        return [
            'account_type' => AccountType::class,
            'status' => AccountStatus::class,
            'opened_date' => 'date',
            'closed_date' => 'date',
            'status' => AccountStatus::class,
            'account_type' => AccountType::class,
        ];


    }


    /**
     * صاحب حساب
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

}
