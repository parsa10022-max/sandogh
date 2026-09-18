<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundStatistic extends Model
{
    protected $fillable = [
        'paid_loans_count',
        'paid_loans_amount',
        'donations_count',
        'statistics_date',
    ];

    protected $casts = [
        'paid_loans_count' => 'integer',
        'paid_loans_amount' => 'integer',
        'donations_count' => 'integer',
        'statistics_date' => 'date',
    ];
}
