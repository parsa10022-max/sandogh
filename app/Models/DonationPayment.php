<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    ];



    protected $casts = [

        'paid_at' => 'datetime',

        'amount' => 'integer',

    ];



    public function account()
    {
        return $this->belongsTo(Account::class);
    }



    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
