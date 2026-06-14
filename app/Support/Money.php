<?php

namespace App\Support;

class Money
{
    public static function currency(): string
    {
        return (string) config('app.currency', 'EGP');
    }

    public static function format(float|int|string|null $amount, ?string $currency = null): string
    {
        $value = (float) ($amount ?? 0);
        $code = $currency ?: self::currency();

        return sprintf('%s %s', strtoupper($code), number_format($value, 2));
    }
}
