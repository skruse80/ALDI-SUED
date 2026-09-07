<?php

use App\Data\PriceUpdateData;

it('rejects a negative amount', function () {
    PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => -1,
    ]);
})->throws(InvalidArgumentException::class, 'Amount must not be negative.');

it('rejects an invalid currency', function () {
    PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 100,
        'currency' => 'euro',
    ]);
})->throws(InvalidArgumentException::class, 'Currency must be a 3-letter ISO code.');

it('rejects a missing sku', function () {
    PriceUpdateData::fromArray([
        'store_code' => 'K-101',
        'amount_cents' => 100,
    ]);
})->throws(InvalidArgumentException::class, 'SKU must not be empty.');
