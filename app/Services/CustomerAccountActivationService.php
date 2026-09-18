<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CustomerAccountActivationService
{
    public function create(
        Customer $customer,
        array $data
    ): User {
        return DB::transaction(function () use ($customer, $data) {

            if ($customer->user()->exists()) {
                throw new RuntimeException(
                    'برای این عضو قبلاً حساب کاربری ایجاد شده است.'
                );
            }

            return User::create([
                'customer_id' => $customer->id,
                'username' => $data['username'],
                'mobile' => $customer->mobile,
                'email' => null,
                'role' => UserRole::CUSTOMER,
                'password' => $data['password'],
                'status' => UserStatus::ACTIVE,
                'mobile_verified_at' => now(),
            ]);
        });
    }
}
