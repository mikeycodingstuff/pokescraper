<?php

namespace Database\Seeders;

use App\Models\Generation;
use App\Models\Pokemon;
use App\Models\Type;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $gens = Generation::factory(5)->create();
        $types = Type::factory(16)->recycle($gens)->create();

        Pokemon::factory(100)
            ->recycle($gens)
            ->create()
            ->each(function ($pokemon) use ($types, $gens) {
                $pokemonGeneration = $gens->find($pokemon->generation_id);

                $validTypes = $types->filter(function ($type) use ($pokemonGeneration) {
                    return $type->generation_id <= $pokemonGeneration->id;
                });

                if ($validTypes->count() >= 2) {
                    // Randomly select 1 or 2 valid types
                    $randomTypes = $validTypes->random(rand(1, 2))->pluck('id');
                } elseif ($validTypes->count() == 1) {
                    // If only one valid type, select that type
                    $randomTypes = $validTypes->pluck('id');
                } else {
                    // If no valid types, set randomTypes to an empty array
                    $randomTypes = collect();
                }

                // Attach the types to the Pokémon if any valid types are available
                if ($randomTypes->isNotEmpty()) {
                    $pokemon->types()->attach($randomTypes);
                }
            });
    }
}
