<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Customer;
use Illuminate\Support\Str;


/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'customer_id' => Customer::factory(),

        'username' => $this->faker->unique()->userName(),

        'mobile' => '09' . $this->faker->unique()->numerify('#########'),

        'password' => '123456',

        'role' => UserRole::CUSTOMER,

        'status' => UserStatus::ACTIVE,

        'mobile_verified_at' => now(),

        'last_login_at' => null,

        'remember_token' => Str::random(10),

    ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::ADMIN,
        ]);
    }
    public function boardMember(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::BOARD_MEMBER,
        ]);
    }
    public function operator(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::OPERATOR,
        ]);
    }
    public function customer(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::CUSTOMER,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::ACTIVE,
        ]);
    }
    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::INACTIVE,
        ]);
    }
    public function blocked(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::BLOCKED,
        ]);
    }


}
