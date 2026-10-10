<?php

namespace Tests\Feature\Customer;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerAccountActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAccountActivationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerAccountActivationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CustomerAccountActivationService::class);
    }

    public function test_creates_customer_user_account(): void
    {
        $customer = Customer::factory()->create([
            'mobile' => '09121234567',
        ]);

        $user = $this->service->create($customer, [
            'username' => 'customer_test',
            'password' => 'Password123!',
        ]);

        $this->assertInstanceOf(User::class, $user);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'customer_id' => $customer->id,
            'username' => 'customer_test',
            'mobile' => '09121234567',
            'role' => UserRole::CUSTOMER->value,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $this->assertNotNull($user->mobile_verified_at);

        $this->assertTrue(
            Hash::check('Password123!', $user->password)
        );
    }

    public function test_rejects_creating_second_account_for_same_customer(): void
    {
        $customer = Customer::factory()->create([
            'mobile' => '09121234567',
        ]);

        $this->service->create($customer, [
            'username' => 'customer_first',
            'password' => 'Password123!',
        ]);

        $this->expectException(\RuntimeException::class);

        $this->expectExceptionMessage(
            'برای این عضو قبلاً حساب کاربری ایجاد شده است.'
        );

        $this->service->create($customer, [
            'username' => 'customer_second',
            'password' => 'Password456!',
        ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_creates_customer_account_with_customer_mobile(): void
    {
        $customer = Customer::factory()->create([
            'mobile' => '09129876543',
        ]);

        $user = $this->service->create($customer, [
            'username' => 'mobile_test',
            'password' => 'Password123!',
        ]);

        $this->assertSame(
            $customer->mobile,
            $user->mobile
        );

        $this->assertSame(
            $customer->id,
            $user->customer_id
        );

        $this->assertSame(
            UserRole::CUSTOMER,
            $user->role
        );

        $this->assertSame(
            UserStatus::ACTIVE,
            $user->status
        );
    }
}
