<?php

namespace Database\Seeders;

use App\Models\LoanType;
use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LoanType::upsert([
            [
                'name' => 'وام مسکن','is_active' => LoanTypeStatus::ACTIVE,
                'prefix' => '2911',
                'description' => null,
                'is_active' => LoanTypeStatus::ACTIVE,
            ],
            [
                'name' => 'وام اشتغال',
                'prefix' => '2511',
                'description' => null,
                'is_active' => LoanTypeStatus::ACTIVE,
            ],
            [
                'name' => 'وام ازدواج',
                'prefix' => '2611',
                'description' => null,
                'is_active' => LoanTypeStatus::ACTIVE,
            ],
            [
                'name' => 'وام سطوح اداره شده',
                'prefix' => '3411',
                'description' => null,
                'is_active' => LoanTypeStatus::ACTIVE,
            ],
        ], ['prefix'], ['name', 'description', 'is_active']);
    }
}
