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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'customer_code' => fake()->unique()->numberBetween(261001, 302000),

            'first_name' =>$this->faker->firstName(),

            'last_name' =>$this->faker->lastName(),

            'father_name' => $this->faker->optional()->firstNameMale(),

            'national_code' => $this->faker->unique()->numerify('##########'),

            'mobile' => '09' . $this->faker->unique()->numerify('#########'),

            'mobile_second' => $this->faker->optional()->passthrough(
                '09' . $this->faker->unique()->numerify('#########')
            ),

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


