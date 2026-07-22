<?php

namespace Database\Seeders;

use App\Enums\LoanTypeStatus;
use App\Models\LoanType;

use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    public function run(): void
    {
        LoanType::upsert([
            [
                'name' => 'وام مسکن',
                'prefix' => '2911',
                'description' => null,
                'status' => LoanTypeStatus::ACTIVE->value,
            ],
            [
                'name' => 'وام اشتغال',
                'prefix' => '2511',
                'description' => null,
                'status' => LoanTypeStatus::ACTIVE->value,
            ],
            [
                'name' => 'وام ازدواج',
                'prefix' => '2611',
                'description' => null,
                'status' => LoanTypeStatus::ACTIVE->value,
            ],
            [
                'name' => 'وام سطوح اداره شده',
                'prefix' => '3411',
                'description' => null,
                'status' => LoanTypeStatus::ACTIVE->value,
            ],
        ],
            ['prefix'],
            ['name', 'description', 'status']);
    }
}
