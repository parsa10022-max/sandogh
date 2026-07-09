<?php

namespace Database\Seeders;
use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder


{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::firstOrCreate(
            ['customer_code' => '1'],
            [
                'national_code' => '2299991190',
                'first_name' => 'مدیر',
                'last_name' => 'سیستم',
                'mobile' => '09120000000',
                'status' => CustomerStatus::ACTIVE,
            ]
        );
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'customer_id' => $customer->id,
                'mobile' => '09120000000',
                'password' => '123456',
                'role' => UserRole::ADMIN,
                'status' => UserStatus::ACTIVE,
            ]
        );
    }

}
