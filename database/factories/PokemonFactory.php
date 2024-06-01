<?php

namespace Database\Factories;

use App\Models\Generation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pokemon>
 */
class PokemonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'generation_id' => Generation::factory(),
            'base_experience' => fake()->numberBetween(1, 100),
            'height' => fake()->numberBetween(1, 100),
            'weight' => fake()->numberBetween(1, 100),
        ];
    }
}
