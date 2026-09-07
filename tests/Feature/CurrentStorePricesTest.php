<?php

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use App\Queries\CurrentStorePrices;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the latest recorded price per store', function () {
    $product = Product::factory()->create();
    $storeA = Store::factory()->create(['code' => 'A-1']);
    $storeB = Store::factory()->create(['code' => 'B-1']);

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $storeA->id,
        'amount_cents' => 100,
        'recorded_at' => now()->subHour(),
    ]);

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $storeA->id,
        'amount_cents' => 150,
        'recorded_at' => now(),
    ]);

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $storeB->id,
        'amount_cents' => 200,
        'recorded_at' => now()->subMinutes(10),
    ]);

    $current = app(CurrentStorePrices::class)->forProduct($product);

    expect($current)->toHaveCount(2)
        ->and($current->firstWhere('store_id', $storeA->id)?->amount_cents)->toBe(150)
        ->and($current->firstWhere('store_id', $storeB->id)?->amount_cents)->toBe(200);
});

it('uses recorded_at rather than insert order when choosing the current price', function () {
    $product = Product::factory()->create();
    $store = Store::factory()->create();

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 300,
        'recorded_at' => now(),
    ]);

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 100,
        'recorded_at' => now()->subDay(),
    ]);

    $current = app(CurrentStorePrices::class)->forProduct($product);

    expect($current)->toHaveCount(1)
        ->and($current->first()?->amount_cents)->toBe(300);
});

it('returns the latest recorded price per product for a store', function () {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 80,
        'recorded_at' => now()->subHour(),
    ]);

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 90,
        'recorded_at' => now(),
    ]);

    $current = app(CurrentStorePrices::class)->forStore($store);

    expect($current)->toHaveCount(1)
        ->and($current->first()?->amount_cents)->toBe(90);
});
