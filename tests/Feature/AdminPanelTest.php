<?php

use App\Filament\Resources\PriceHistories\Pages\ListPriceHistories;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Stores\Pages\ListStores;
use App\Filament\Resources\Stores\Pages\ViewStore;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects guests to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('shows the admin login page', function () {
    $this->get('/admin/login')->assertOk();
});

it('lets an authenticated user open the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk();
});

it('lists products in the admin panel', function () {
    $this->actingAs(User::factory()->create());
    Product::factory()->create(['sku' => 'MILCH-001', 'name' => 'Vollmilch 1l']);

    Livewire::test(ListProducts::class)
        ->assertSuccessful()
        ->assertSee('MILCH-001')
        ->assertSee('Vollmilch 1l');
});

it('lists stores in the admin panel', function () {
    $this->actingAs(User::factory()->create());
    Store::factory()->create(['code' => 'K-101', 'name' => 'Köln Süd']);

    Livewire::test(ListStores::class)
        ->assertSuccessful()
        ->assertSee('K-101');
});

it('records a new price from the product table', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101', 'name' => 'Köln Süd']);

    Livewire::test(ListProducts::class)
        ->callAction(TestAction::make('recordPrice')->table($product), [
            'store_code' => 'K-101',
            'amount_cents' => 149,
        ]);

    $this->assertDatabaseHas('price_histories', [
        'product_id' => $product->id,
        'amount_cents' => 149,
        'currency' => 'EUR',
    ]);
});

it('shows current store prices on the product view page', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['sku' => 'MILCH-001', 'name' => 'Vollmilch 1l']);
    $store = Store::factory()->create(['code' => 'K-101', 'name' => 'Köln Süd']);
    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 129,
    ]);

    Livewire::test(ViewProduct::class, ['record' => $product->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('MILCH-001')
        ->assertSee('K-101')
        ->assertSee('1,29');
});

it('shows current product prices on the store view page', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['sku' => 'MILCH-001', 'name' => 'Vollmilch 1l']);
    $store = Store::factory()->create(['code' => 'K-101', 'name' => 'Köln Süd']);
    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 129,
    ]);

    Livewire::test(ViewStore::class, ['record' => $store->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('K-101')
        ->assertSee('MILCH-001')
        ->assertSee('1,29');
});

it('lists price history as read-only', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['sku' => 'MILCH-001']);
    $store = Store::factory()->create(['code' => 'K-101']);
    PriceHistory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 129,
    ]);

    Livewire::test(ListPriceHistories::class)
        ->assertSuccessful()
        ->assertSee('MILCH-001')
        ->assertSee('K-101')
        ->assertDontSee('Create');
});
