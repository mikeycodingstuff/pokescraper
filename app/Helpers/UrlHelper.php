<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class UrlHelper
{
    public static function getIdFromUrl(string $url): int
    {
        return (int)Str::of($url)->beforeLast('/')->afterLast('/')->toString();
    }
}
