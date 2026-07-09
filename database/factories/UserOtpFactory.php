<?php

namespace Database\Factories;

use App\Enums\UserOtpStatus;
use App\Enums\UserOtpType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserOtpFactory extends Factory
{
    public function definition(): array
    {
        return [

            'user_id' => User::factory(),

            'mobile' => '09' . fake()->unique()->numerify('#########'),

            'code' => Hash::make('123456'),

            'type' => UserOtpType::LOGIN,

            'status' => UserOtpStatus::PENDING,

            'attempts' => 0,

            'expires_at' => now()->addMinutes(2),

            'verified_at' => null,

            'cancelled_at' => null,

            'ip_address' => fake()->ipv4(),

            'user_agent' => fake()->userAgent(),

        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [

            'status' => UserOtpStatus::EXPIRED,

            'expires_at' => now()->subMinute(),

        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [

            'status' => UserOtpStatus::VERIFIED,

            'verified_at' => now(),

        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [

            'status' => UserOtpStatus::CANCELLED,

            'cancelled_at' => now(),

        ]);
    }
}
