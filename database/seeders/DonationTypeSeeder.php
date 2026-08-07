<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DonationType;
use App\Models\Account;

class DonationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            [
                'title' => 'کمک به صندوق',
                'account_number' => '7512',
            ],

            [
                'title' => 'باقی‌الصالحات',
                'account_number' => '61129277',
            ],

        ];


        foreach ($items as $item) {

            $account = Account::where(
                'account_number',
                $item['account_number']
            )->first();


            if (! $account) {
                continue;
            }


            DonationType::updateOrCreate(

                [
                    'account_id' => $account->id,
                ],

                [
                    'title' => $item['title'],
                    'is_active' => true,
                ]

            );

        }
    }
}
