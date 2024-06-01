<?php

namespace Helpers;

use Illuminate\Support\Str;
use InvalidArgumentException;

class RomanNumeralsConverter
{
    protected static array $lookup = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    ];

    public static function convertToRomanNumeral(int $int, bool $lowercase = false): string
    {
        $solution = '';

        if ($int < 1 || $int > 4999) {
            throw new InvalidArgumentException('Input int must be between 1 and 4999.');
        }

        foreach (static::$lookup as $glyph => $value) {
            while ($int >= $value) {
                $solution .= $glyph;
                $int -= $value;
            }
        }

        return $lowercase ? $solution : Str::lower($solution);
    }

    public static function convertToInteger(string $numeral): int
    {
        if (empty($numeral)) {
            throw new InvalidArgumentException('Input must not be null or empty.');
        }

        $integer = 0;
        $prevValue = 0;

        for ($i = 0; $i < strlen($numeral); $i++) {
            $currentNumeral = $numeral[$i];

            if (!isset(static::$lookup[$currentNumeral])) {
                throw new InvalidArgumentException("$numeral is not a valid Roman numeral.");
            }

            $currentValue = static::$lookup[$currentNumeral];

            if ($currentValue > $prevValue) {
                $integer += $currentValue - 2 * $prevValue;
            } else {
                $integer += $currentValue;
            }

            $prevValue = $currentValue;
        }

        return $integer;
    }
}
