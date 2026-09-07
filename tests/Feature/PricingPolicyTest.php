<?php

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Policies\PriceHistoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\StorePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('forbids deleting a product that already has price history', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $store = Store::factory()->create();

    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 129,
    ]);

    expect(app(ProductPolicy::class)->delete($user, $product))->toBeFalse();
});

it('allows deleting a product without price history', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    expect(app(ProductPolicy::class)->delete($user, $product))->toBeTrue();
});

it('forbids deleting a store that already has price history', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    PriceHistory::factory()->create(['store_id' => $store->id]);

    expect(app(StorePolicy::class)->delete($user, $store))->toBeFalse();
});

it('allows deleting a store without price history', function () {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    expect(app(StorePolicy::class)->delete($user, $store))->toBeTrue();
});

it('forbids mutating price history through policies', function () {
    $user = User::factory()->create();
    $history = PriceHistory::factory()->create();

    $policy = app(PriceHistoryPolicy::class);

    expect($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $history))->toBeFalse()
        ->and($policy->delete($user, $history))->toBeFalse()
        ->and($policy->view($user, $history))->toBeTrue();
});
