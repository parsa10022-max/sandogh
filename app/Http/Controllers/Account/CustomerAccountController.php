<?php

namespace App\Http\Controllers\Account;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Customer;

class CustomerAccountController extends Controller
{
    public function create(Customer $customer)
    {
        return view(
            'accounts.customer-create',
            compact('customer')
        );
    }

    public function store(
        StoreAccountRequest $request,
        Customer $customer
    ) {
        $data = $request->validated();

        $exists = $customer->accounts()
            ->where('account_type', $data['account_type'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'account_type' =>
                        'این نوع حساب قبلاً برای این مشتری تعریف شده است.',
                ])
                ->withInput();
        }

        $customer->accounts()->create([
            'account_number' => $data['account_number'],
            'account_type' => $data['account_type'],
            'balance' => $data['balance'],
            'status' => AccountStatus::ACTIVE,
            'opened_date' => now(),
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'حساب مشتری با موفقیت تعریف شد.');
    }

    public function edit(
        Customer $customer,
        Account $account
    ) {
        abort_unless(
            $account->customer_id === $customer->id,
            404
        );

        return view(
            'accounts.customer-edit',
            compact('customer', 'account')
        );
    }
    public function update(
        UpdateAccountRequest $request,
        Customer $customer,
        Account $account
    ) {
        abort_unless(
            $account->customer_id === $customer->id,
            404
        );

        $data = $request->validated();

        $exists = $customer->accounts()
            ->where('account_type', $data['account_type'])
            ->where('id', '!=', $account->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'account_type' =>
                        'این نوع حساب قبلاً برای این مشتری تعریف شده است.',
                ])
                ->withInput();
        }

        $account->update([
            'account_number' => $data['account_number'],
            'account_type' => $data['account_type'],
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'حساب با موفقیت ویرایش شد.');
    }
}
