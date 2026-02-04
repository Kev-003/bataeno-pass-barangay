<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Barangay;
use App\Models\BarangayTerm;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BarangayTerm>
 */
class BarangayTermFactory extends Factory
{
    protected $model = BarangayTerm::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'barangay_id' => Barangay::factory(),
            'position_type' => $this->faker->randomElement(['Captain', 'Secretary', 'Kagawad', 'Treasurer']),
            'started_at' => now()->subMonths(6),
            'ended_at' => now()->addMonths(18),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'started_at' => now()->subYears(2),
            'ended_at' => now()->subYear(),
        ]);
    }
}
