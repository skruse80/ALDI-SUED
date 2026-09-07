<?php

namespace App\Support;

final class Money
{
    public static function format(int $cents, string $currency = 'EUR'): string
    {
        return number_format($cents / 100, 2, ',', '.').' '.$currency;
    }
}
