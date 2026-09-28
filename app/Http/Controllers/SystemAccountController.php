<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemAccountController extends Controller
{
    public function index(): View
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

    public function create(): View
    {
        return view(
            'system-accounts.create'
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'account_number' => [
                'required',
                'string',
                'max:100',
                'unique:accounts,account_number',
            ],
        ]);

        Account::create([
            'name' => trim($data['name']),

            'account_number' => trim($data['account_number']),

            'account_type' => AccountType::SYSTEM,

            'balance' => 0,

            'status' => AccountStatus::ACTIVE,

            'opened_date' => now(),
        ]);

        return redirect()
            ->route('system-accounts.index')
            ->with(
                'success',
                'حساب سیستمی ایجاد شد.'
            );
    }

    public function edit(Account $systemAccount): View
    {
        $this->ensureSystemAccount($systemAccount);

        return view(
            'system-accounts.edit',
            compact('systemAccount')
        );
    }

    public function update(
        Request $request,
        Account $systemAccount
    ): RedirectResponse {
        $this->ensureSystemAccount($systemAccount);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $systemAccount->update([
            'name' => trim($data['name']),
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
        $this->ensureSystemAccount($systemAccount);

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

    /**
     * اطمینان از اینکه حساب واقعاً سیستمی است.
     */
    private function ensureSystemAccount(Account $account): void
    {
        abort_unless(
            $account->account_type === AccountType::SYSTEM,
            404
        );
    }
}
