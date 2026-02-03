<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Household;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HouseholdMemberProfile>
 */
class HouseholdMemberProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'household_id' => Household::factory(),
            'role' => fake()->randomElement(['Head', 'Member', 'Dependent']),
            'membership_type' => 'Resident',
            'presence_status' => 'Present',
            'economic_contribution' => fake()->randomElement(['Employed', 'Unemployed', 'Student']),
            'monthly_income' => fake()->randomFloat(2, 0, 30000),
            'started_at' => now(),
        ];
    }
}
