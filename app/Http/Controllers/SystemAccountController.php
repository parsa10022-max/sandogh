<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Enums\AccountStatus;

use Illuminate\Http\RedirectResponse;



class SystemAccountController extends Controller
{

    public function index()
    {
        $accounts = Account::query()
            ->where(
                'account_type',
                AccountType::SYSTEM
            )
            ->latest()
            ->paginate(15);


        return view(
            'system-accounts.index',
            compact('accounts')
        );
    }


    public function create()
    {
        return view(
            'system-accounts.create'
        );
    }


    public function store(Request $request)
    {
        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'account_number' => [
                'required',
                'unique:accounts,account_number'
            ],

        ]);


        Account::create([

            'name' => $data['name'],

            'account_number'
            => $data['account_number'],

            'account_type'
            => AccountType::SYSTEM,

            'balance' => 0,

            'status' => 1,

            'opened_date' => now(),

        ]);


        return redirect()
            ->route('system-accounts.index')
            ->with(
                'success',
                'حساب سیستمی ایجاد شد.'
            );
    }

    public function edit(Account $systemAccount)
    {
        return view(
            'system-accounts.edit',
            compact('systemAccount')
        );
    }

    public function update(
        Request $request,
        Account $systemAccount
    )
    {

        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255'
            ],

        ]);


        $systemAccount->update([
            'name' => $data['name'],
        ]);


        return redirect()
            ->route('system-accounts.index')
            ->with(
                'success',
                'حساب ویرایش شد.'
            );
    }

    /**
     * تغییر وضعیت حساب سیستمی
     */



    public function changeStatus(
        Account $systemAccount
    ): RedirectResponse {

        $systemAccount->update([

            'status' => $systemAccount->status === AccountStatus::ACTIVE
                ? AccountStatus::CLOSED
                : AccountStatus::ACTIVE,

        ]);

        return redirect()
            ->route('system-accounts.index')
            ->with(
                'success',
                'وضعیت حساب با موفقیت تغییر کرد.'
            );
    }
}
