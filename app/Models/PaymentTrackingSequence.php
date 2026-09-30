<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTrackingSequence extends Model
{
    protected $fillable = [
        'jalali_date',
        'last_sequence',
    ];

    protected $casts = [
        'last_sequence' => 'integer',
    ];
}
