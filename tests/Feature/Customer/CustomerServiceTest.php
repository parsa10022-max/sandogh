<?php

namespace Tests\Feature\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\CustomerStatus;
use App\Models\Account;
use App\Models\Customer;
use App\Models\User;
use App\Services\Customer\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private function validData(
        int $initialBalance = 0,
        int $accountType = 1,
    ): array {
        return [
            'customer_code' => 'CUS-' . fake()->unique()->numerify('######'),
            'first_name' => 'رضا',
            'last_name' => 'احمدی',
            'father_name' => 'علی',
            'national_code' => fake()->unique()->numerify('##########'),
            'mobile' => fake()->unique()->numerify('0912#######'),
            'mobile_second' => null,
            'iban' => null,
            'account_type' => $accountType,
            'account_number_suffix' => fake()->unique()->numerify('########'),
            'initial_balance' => $initialBalance,
        ];
    }

    public function test_creates_customer_and_account_without_initial_balance(): void
    {
        $service = app(CustomerService::class);

        $data = $this->validData();

        $customer = $service->create($data);

        $this->assertInstanceOf(Customer::class, $customer);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'customer_code' => $data['customer_code'],
            'first_name' => 'رضا',
            'last_name' => 'احمدی',
            'status' => CustomerStatus::ACTIVE->value,
        ]);

        $this->assertDatabaseHas('accounts', [
            'customer_id' => $customer->id,
            'account_number' => '6111-' . $data['account_number_suffix'],
            'account_type' => AccountType::SAVING->value,
            'balance' => 0,
            'status' => AccountStatus::ACTIVE->value,
        ]);

        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_transactions', 0);
    }

    public function test_creates_customer_and_records_initial_balance(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $service = app(CustomerService::class);

        $initialBalance = 5_000_000;

        $data = $this->validData(
            initialBalance: $initialBalance
        );

        $customer = $service->create($data);

        $account = Account::query()
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $this->assertSame(
            $initialBalance,
            $account->balance
        );

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'amount' => $initialBalance,
            'balance_before' => 0,
            'balance_after' => $initialBalance,
            'created_by' => $user->id,
            'description' => 'موجودی اولیه هنگام افتتاح حساب',
        ]);

        $this->assertDatabaseCount('account_transactions', 1);
    }

    public function test_initial_balance_zero_does_not_create_transaction(): void
    {
        $service = app(CustomerService::class);

        $customer = $service->create(
            $this->validData(initialBalance: 0)
        );

        $account = Account::query()
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $this->assertSame(0, $account->balance);

        $this->assertDatabaseCount('account_transactions', 0);
    }

    public function test_update_changes_customer_data(): void
    {
        $customer = Customer::factory()->create([
            'first_name' => 'رضا',
            'last_name' => 'احمدی',
        ]);

        $service = app(CustomerService::class);

        $updated = $service->update($customer, [
            'first_name' => 'محمد',
            'last_name' => 'کریمی',
        ]);

        $this->assertSame('محمد', $updated->first_name);
        $this->assertSame('کریمی', $updated->last_name);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'first_name' => 'محمد',
            'last_name' => 'کریمی',
        ]);
    }

    public function test_delete_soft_deletes_customer(): void
    {
        $customer = Customer::factory()->create();

        $service = app(CustomerService::class);

        $result = $service->delete($customer);

        $this->assertTrue($result);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_find_returns_customer(): void
    {
        $customer = Customer::factory()->create();

        $service = app(CustomerService::class);

        $result = $service->find($customer->id);

        $this->assertNotNull($result);
        $this->assertSame($customer->id, $result->id);
    }

    public function test_find_returns_null_for_missing_customer(): void
    {
        $service = app(CustomerService::class);

        $result = $service->find(999999);

        $this->assertNull($result);
    }

    public function test_change_status_updates_customer_status(): void
    {
        $customer = Customer::factory()->create([
            'status' => CustomerStatus::ACTIVE,
        ]);

        $service = app(CustomerService::class);

        $updated = $service->changeStatus(
            $customer,
            CustomerStatus::INACTIVE
        );

        $this->assertSame(
            CustomerStatus::INACTIVE,
            $updated->status
        );

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => CustomerStatus::INACTIVE->value,
        ]);
    }

    public function test_restore_restores_archived_customer(): void
    {
        $customer = Customer::factory()->create();

        $customer->delete();

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);

        $service = app(CustomerService::class);

        $service->restore($customer->id);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);

        $this->assertNull(
            Customer::withTrashed()
                ->find($customer->id)
                ->deleted_at
        );
    }

    public function test_get_active_returns_only_active_customers(): void
    {
        Customer::factory()->create([
            'status' => CustomerStatus::ACTIVE,
        ]);

        Customer::factory()->create([
            'status' => CustomerStatus::INACTIVE,
        ]);

        $service = app(CustomerService::class);

        $customers = $service->getActive();

        $this->assertCount(1, $customers);

        $this->assertSame(
            CustomerStatus::ACTIVE,
            $customers->first()->status
        );
    }

    public function test_get_archived_returns_soft_deleted_customers(): void
    {
        $active = Customer::factory()->create();
        $archived = Customer::factory()->create();

        $archived->delete();

        $service = app(CustomerService::class);

        $result = $service->getArchived();

        $this->assertCount(1, $result->items());

        $this->assertSame(
            $archived->id,
            $result->items()[0]->id
        );

        $this->assertNotSame(
            $active->id,
            $result->items()[0]->id
        );
    }
}
