<?php

namespace App\Services\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\CustomerStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Customer;
use App\Services\Account\AccountService;
use App\Services\Account\AccountTransactionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly AccountTransactionService $accountTransactionService,
    ) {
    }

    public function getPaginated(
        int $perPage = 15,
        ?string $search = null
    ): LengthAwarePaginator {
        return Customer::query()
            ->search($search)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * ایجاد مشتری به همراه حساب
     */
    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {

            $customerData = [
                'customer_code' => $data['customer_code'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'father_name' => $data['father_name'] ?? null,
                'national_code' => $data['national_code'],
                'mobile' => $data['mobile'],
                'mobile_second' => $data['mobile_second'] ?? null,
                'iban' => $data['iban'] ?? null,
                'status' => CustomerStatus::ACTIVE,
            ];

            $customer = Customer::create($customerData);

            $accountType = AccountType::from(
                (int) $data['account_type']
            );

            $accountNumber = $accountType->prefix()
                . '-'
                . $data['account_number_suffix'];

            $account = $customer->accounts()->create([
                'account_number' => $accountNumber,
                'account_type' => $accountType,
                'balance' => 0,
                'status' => AccountStatus::ACTIVE,
                'opened_date' => now(),
            ]);

            $initialBalance = (int) $data['initial_balance'];

            if ($initialBalance > 0) {
                $balanceBefore = $account->balance;

                $this->accountService->depositBalance(
                    account: $account,
                    amount: $initialBalance,
                );

                $balanceAfter = $balanceBefore + $initialBalance;

                $this->accountTransactionService->create(
                    account: $account->fresh(),
                    type: TransactionType::DEPOSIT,
                    source: TransactionSource::OPERATOR,
                    paymentMethod: PaymentMethod::BANK_TRANSFER,
                    amount: $initialBalance,
                    balanceBefore: $balanceBefore,
                    balanceAfter: $balanceAfter,
                    createdBy: auth()->id(),
                    description: 'موجودی اولیه هنگام افتتاح حساب',
                );
            }

            return $customer;
        });
    }

    /**
     * بروزرسانی اطلاعات مشتری
     */
    public function update(
        Customer $customer,
        array $data
    ): Customer {
        $customer->update($data);

        return $customer->fresh();
    }

    /**
     * حذف مشتری
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * دریافت مشتری
     */
    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * تغییر وضعیت مشتری
     */
    public function changeStatus(
        Customer $customer,
        CustomerStatus $status
    ): Customer {
        $customer->update([
            'status' => $status,
        ]);

        return $customer->fresh();
    }

    public function getArchived(
        int $perPage = 15
    ): LengthAwarePaginator {
        return Customer::onlyTrashed()
            ->latest()
            ->paginate($perPage);
    }

    public function restore(int $id): void
    {
        Customer::onlyTrashed()
            ->findOrFail($id)
            ->restore();
    }

    public function getActive(): Collection
    {
        return Customer::query()
            ->active()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }
}
