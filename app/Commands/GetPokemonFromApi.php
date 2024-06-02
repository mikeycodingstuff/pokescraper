<?php

namespace App\Commands;

use App\Models\Generation;
use App\Models\Pokemon;
use App\Models\Type;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class GetPokemonFromApi extends Command
{
    protected $signature = 'fetch:all';
    protected $description = 'Get pokemon from API.';

    public function handle()
    {
        $url = "https://pokeapi.co/api/v2/pokemon?limit=100000&offset=0";
        $response = Http::get($url);
        $pokemonUrls = collect($response->json('results'))->pluck('url');
        $count = 0;
        $pokemonUrls->each(function ($url) use (&$count) {
            if ($count < 100) {
                $this->fetchAndStorePokemon($url);
                dump($count);
                $count++;
            }
        });
    }

    private function fetchAndStorePokemon(string $url)
    {
        $data = Http::get($url)->json();

        $speciesUrl = $data['species']['url'];
        $generationData = $this->getGenerationInfoFromSpeciesUrl($speciesUrl);
        $generation = $this->fetchAndStoreGeneration($generationData['url'], $generationData['name']);

        $pokemon = Pokemon::updateOrCreate(
            ['id' => $data['id']],
            [
                'name' => $data['name'],
                'generation_id' => $generation->id,
                'base_experience' => $data['base_experience'],
                'height' => $data['height'],
                'weight' => $data['weight'],
            ]
        );

        $typeData = collect($data['types'])->pluck('type');
        $typeData->each(function ($type) use ($pokemon) {
            $type = $this->fetchAndStoreType($type['url'], $type['name']);
            if (!$pokemon->types->contains($type->id)) {
                $pokemon->types()->attach($type);
            }
        });

        $pokemon->refresh();
    }

    private function fetchAndStoreGeneration(string $url, string $name): Generation
    {
        $generation = Generation::query()->firstWhere('name', $name);

        if (!$generation) {
            $data = Http::get($url)->json();

            $generation = Generation::firstOrCreate(
                [
                    'id' => $data['id'],
                    'name' => $data['name'],
                ],
                [
                    'main_region' => $data['main_region']['name'],
                ]
            );
        }

        return $generation;
    }

    private function getGenerationInfoFromSpeciesUrl(string $url): array
    {
        return Http::get($url)->json('generation');
    }

    private function fetchAndStoreType(string $url, string $name): Type
    {
        $type = Type::query()->firstWhere('name', $name);

        if (!$type) {
            $data = Http::get($url)->json();

            $generation = $this->fetchAndStoreGeneration($data['generation']['url'], $data['generation']['name']);

            $type = Type::firstOrCreate(
                [
                    'id' => $data['id'],
                    'name' => $data['name'],
                ],
                [
                    'generation_id' => $generation->id,
                ]
            );
        }

        return $type;
    }
}
