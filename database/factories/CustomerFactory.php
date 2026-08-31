<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $firstNames = [
            'محمد',
            'علی',
            'رضا',
            'مهدی',
            'حسین',
            'احمد',
            'امیر',
            'مجتبی',
            'مصطفی',
            'سعید',
            'حمید',
            'محسن',
            'حسن',
            'عباس',
            'محمود',
            'اکبر',
            'جواد',
            'مرتضی',
            'رضوان',
            'یاسر',
        ];

        $lastNames = [
            'رضایی',
            'احمدی',
            'کریمی',
            'حسینی',
            'مرادی',
            'محمدی',
            'موسوی',
            'حیدری',
            'اکبری',
            'صادقی',
            'جعفری',
            'کاظمی',
            'عباسی',
            'زارعی',
            'رحیمی',
            'نوری',
            'رستمی',
            'سلیمانی',
            'حسنی',
            'قاسمی',
        ];

        $fatherNames = [
            'حسن',
            'حسین',
            'علی',
            'محمد',
            'محمود',
            'احمد',
            'رضا',
            'مهدی',
            'اکبر',
            'عباس',
        ];

        return [
            'customer_code' => fake()->unique()->numberBetween(303001, 999999),

            'first_name' => fake()->randomElement($firstNames),

            'last_name' => fake()->randomElement($lastNames),

            'father_name' => fake()->randomElement($fatherNames),

            'national_code' => fake()->unique()->numerify('##########'),

            'mobile' => '09' . fake()->unique()->numerify('#########'),

            'mobile_second' => null,

            'status' => CustomerStatus::ACTIVE,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => CustomerStatus::ACTIVE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => CustomerStatus::INACTIVE,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn () => [
            'status' => CustomerStatus::BLOCKED,
        ]);
    }
}

