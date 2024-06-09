<?php

namespace App\Commands;

use App\Helpers\UrlHelper;
use App\Models\Generation;
use App\Models\Pokemon;
use App\Models\Type;
use App\Traits\GetsResourceIndexRouteData;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class FetchPokeapiData extends Command
{
    use GetsResourceIndexRouteData;

    protected $signature = 'fetch:all';
    protected $description = 'Get pokemon from API.';

    public function handle(): void
    {
        $this->fetchGens();
        $this->fetchTypes();
        $this->fetchPokemons();
    }

    protected function fetchGens(): void
    {
        $items = $this->getGenList();

        try {
            $this->info('Fetching generations...');

            DB::transaction(function () use ($items) {
                $this->withProgressBar($items, function ($item) {
                    $this->fetchAndStoreGen($item);
                });
            });

            $this->newLine();
            $this->info('Successfully fetched gens.');
        } catch (Exception $e) {
            $this->error('An error occurred fetching gens.');
            $this->error($e->getMessage());
        }
    }

    protected function fetchTypes(): void
    {
        $items = $this->getTypeList();

        try {
            $this->info('Fetching types...');

            DB::transaction(function () use ($items) {
                $this->withProgressBar($items, function ($item) {
                    $this->fetchAndStoreType($item);
                });
            });

            $this->newLine();
            $this->info('Successfully fetched types.');
        } catch (Exception $e) {
            $this->error('An error occurred fetching types.');
            $this->error($e->getMessage());
        }
    }

    protected function fetchPokemons(): void
    {
        $items = $this->getPokemonList();

        try {
            $this->info('Fetching pokemon...');

            DB::transaction(function () use ($items) {
                $this->withProgressBar($items, function ($item) {
                    $this->fetchAndStorePokemon($item);
                });
            });

            $this->newLine();
            $this->info('Successfully fetched types.');
        } catch (Exception $e) {
            $this->error('An error occurred fetching types.');
            $this->error($e->getMessage());
        }
    }

    protected function fetchAndStoreGen(array $item): Generation
    {
        $name = $item['name'];
        $url = $item['url'];

        $id = UrlHelper::getIdFromUrl($url);

        $gen = Generation::query()
            ->where('id', $id)
            ->where('name', $name)
            ->first();

        if (!$gen) {
            $data = Http::get($url)->json();

            $gen = Generation::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'main_region' => $data['main_region']['name'],
            ]);
        }

        return $gen;
    }

    protected function fetchAndStoreType(array $item): Type
    {
        $name = $item['name'];
        $url = $item['url'];

        $id = UrlHelper::getIdFromUrl($url);

        $type = Type::query()
            ->where('id', $id)
            ->where('name', $name)
            ->first();

        if (!$type) {
            $data = Http::get($url)->json();

            $generation = $this->fetchAndStoreGen($data['generation']);

            $type = Type::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'generation_id' => $generation->id,
            ]);
        }

        return $type;
    }

    protected function fetchAndStorePokemon(array $item): Pokemon
    {
        $name = $item['name'];
        $url = $item['url'];

        $id = UrlHelper::getIdFromUrl($url);

        $pokemon = Pokemon::query()
            ->where('id', $id)
            ->where('name', $name)
            ->first();

        if (!$pokemon) {
            $data = Http::get($url)->json();

            $genItem = Http::get($data['species']['url'])->json('generation');
            $gen = $this->fetchAndStoreGen($genItem);

            $pokemon = Pokemon::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'generation_id' => $gen->id,
                'base_experience' => $data['base_experience'],
                'height' => $data['height'],
                'weight' => $data['weight'],
            ]);

            $typeItems = collect($data['types'])->pluck('type');

            $typeItems->each(function ($typeItem) use ($pokemon) {
                $type = $this->fetchAndStoreType($typeItem);
                if (!$pokemon->types->contains($type->id)) {
                    $pokemon->types()->attach($type);
                }
            });

            $pokemon->refresh();
        }

        return $pokemon;
    }
}
