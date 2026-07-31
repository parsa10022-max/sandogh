<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('customer')
            ->latest()
            ->paginate(15);

        return view('accounts.index', compact('accounts'));
    }

    public function show(Account $account)
    {
        $account->load('customer');

        return view('accounts.show', compact('account'));
    }

    public function transactions(Account $account)
    {
        $transactions = $account->transactions()
            ->latest()
            ->paginate(20);

        return view(
            'accounts.transactions',
            compact('account', 'transactions')
        );
    }
}

