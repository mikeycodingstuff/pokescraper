<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

trait GetsResourceIndexRouteData
{
    protected function getList(string $key): Collection
    {
        $url = config("urls.$key");
        $response = Http::get($url);

        return collect($response->json('results'));
    }

    protected function getGenList(): Collection
    {
        return $this->getList('gen_list');
    }

    protected function getTypeList(): Collection
    {
        return $this->getList('type_list');
    }

    protected function getPokemonList(): Collection
    {
        return $this->getList('pokemon_list');
    }
}
