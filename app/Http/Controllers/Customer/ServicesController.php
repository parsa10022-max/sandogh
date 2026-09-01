<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServicesController extends Controller
{
    public function index(): View
    {
        $customer = Auth::user()->customer;

        $savingsAccount = Account::query()
            ->where('customer_id', $customer->id)
            ->where('account_type', AccountType::SAVING)
            ->first();

        $currentAccount = Account::query()
            ->where('customer_id', $customer->id)
            ->where('account_type', AccountType::CURRENT)
            ->first();

        return view('customer.services.index', [
            'customer' => $customer,
            'savingsAccount' => $savingsAccount,
            'currentAccount' => $currentAccount,
        ]);
    }
}
