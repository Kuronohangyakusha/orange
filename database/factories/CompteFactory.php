<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'numero_compte' => 'FR'.$this->faker->unique()->numberBetween(1000000000, 9999999999),
            'solde' => $this->faker->randomFloat(2, 0, 10000),
            'type' => $this->faker->randomElement(['courant', 'cheque', 'epargne']),
        ];
    }
}
