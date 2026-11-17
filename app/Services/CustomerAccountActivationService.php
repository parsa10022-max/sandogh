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

            $lockedCustomer = Customer::query()
                ->whereKey($customer->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedCustomer) {
                throw new RuntimeException(
                    'عضو موردنظر پیدا نشد.'
                );
            }

            if (! $lockedCustomer->online_activation_approved) {
                throw new RuntimeException(
                    'مجوز فعال‌سازی اینترنتی این عضو تأیید نشده یا لغو شده است.'
                );
            }

            if ($lockedCustomer->user()->exists()) {
                throw new RuntimeException(
                    'برای این عضو قبلاً حساب کاربری ایجاد شده است.'
                );
            }

            return User::create([
                'customer_id' => $lockedCustomer->id,
                'username' => $data['username'],
                'mobile' => $lockedCustomer->mobile,
                'email' => null,
                'role' => UserRole::CUSTOMER,
                'password' => $data['password'],
                'status' => UserStatus::ACTIVE,
                'mobile_verified_at' => now(),
            ]);
        });
    }
}
