<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $donationAccounts = Account::query()
            ->where('account_type', AccountType::SYSTEM)
            ->where('status', AccountStatus::ACTIVE)
            ->orderBy('account_number')
            ->get();

        return view(
            'customer.dashboard',
            compact(
                'user',
                'donationAccounts'
            )
        );
    }
}
