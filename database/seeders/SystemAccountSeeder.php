<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Enums\AccountType;
use App\Enums\AccountStatus;

class SystemAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [

            [
                'account_number' => '7512',
                'account_type' => AccountType::SYSTEM,
                'balance' => 0,
                'status' => AccountStatus::ACTIVE,
                'opened_date' => now()->toDateString(),
            ],

            [
                'account_number' => '61129277',
                'account_type' => AccountType::SYSTEM,
                'balance' => 0,
                'status' => AccountStatus::ACTIVE,
                'opened_date' => now()->toDateString(),
            ],

        ];


        foreach ($accounts as $data) {

            Account::updateOrCreate(

                [
                    'account_number' => $data['account_number'],
                ],

                $data

            );

        }
    }
}
