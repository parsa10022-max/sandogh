<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $accountType = $request->account_type;


        $accounts = Account::with('customer')

            ->when($search, function ($query) use ($search) {

                $search = str_replace('-', '', $search);


                $query->whereRaw(
                    "REPLACE(account_number, '-', '') LIKE ?",
                    ["%{$search}%"]
                )


                    ->orWhere('name', 'like', "%{$search}%")


                    ->orWhereHas('customer', function ($q) use ($search) {

                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('national_code', 'like', "%{$search}%");

                    });

            })


            ->when($accountType, function ($query) use ($accountType) {

                $query->where(
                    'account_type',
                    $accountType
                );

            })


            ->latest()

            ->paginate(15)

            ->withQueryString();



        // آمار بالای صفحه

        $totalAccounts = Account::count();


        $totalBalance = Account::sum('balance');



        return view(
            'accounts.index',
            compact(
                'accounts',
                'totalAccounts',
                'totalBalance'
            )
        );
    }

    public function show(Account $account)
    {
        $account->load('customer');

        return view('accounts.show', compact('account'));
    }

    public function transactions(Account $account)
    {
        $transactions = $account->transactions()
            ->with('creator')
            ->latest('transaction_date')
            ->paginate(15);


        $summary = [
            'balance' => $account->balance,

            'count' => $account->transactions()
                ->count(),

            'last' => $account->transactions()
                ->latest('transaction_date')
                ->first(),
        ];


        return view(
            'accounts.transactions',
            compact(
                'account',
                'transactions',
                'summary'
            )
        );
    }
}

