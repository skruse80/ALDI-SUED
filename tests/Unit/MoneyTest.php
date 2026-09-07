<?php

use App\Support\Money;

it('formats cents as a German euro amount', function () {
    expect(Money::format(129))->toBe('1,29 EUR')
        ->and(Money::format(100))->toBe('1,00 EUR')
        ->and(Money::format(0))->toBe('0,00 EUR');
});
