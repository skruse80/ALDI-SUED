<?php

use App\Actions\UpdateProductPriceAction;
use App\Data\PriceUpdateData;
use App\Exceptions\TraceIdConflictException;
use App\Jobs\ProcessPriceUpdateJob;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('appends a price history row for a known sku and store', function () {
    $product = Product::factory()->create(['sku' => 'MILCH-001']);
    $store = Store::factory()->create(['code' => 'K-101']);

    $history = (new UpdateProductPriceAction)->execute(PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'currency' => 'eur',
        'trace_id' => '550e8400-e29b-41d4-a716-446655440000',
    ]));

    expect($history)
        ->amount_cents->toBe(129)
        ->currency->toBe('EUR')
        ->trace_id->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->product_id->toBe($product->id)
        ->store_id->toBe($store->id);

    $this->assertDatabaseCount('price_histories', 1);
});

it('keeps previous prices when the same product is updated again', function () {
    $product = Product::factory()->create(['sku' => 'MILCH-001']);
    $store = Store::factory()->create(['code' => 'K-101']);
    $action = new UpdateProductPriceAction;

    $action->execute(PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
    ]));

    $action->execute(PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 139,
    ]));

    expect(PriceHistory::query()->where('product_id', $product->id)->where('store_id', $store->id)->pluck('amount_cents')->all())
        ->toBe([129, 139]);
});

it('replays the same trace id without inserting a second history row', function () {
    $product = Product::factory()->create(['sku' => 'MILCH-001']);
    $store = Store::factory()->create(['code' => 'K-101']);
    $action = new UpdateProductPriceAction;
    $payload = [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'trace_id' => '550e8400-e29b-41d4-a716-446655440200',
    ];

    $first = $action->execute(PriceUpdateData::fromArray($payload));
    $second = $action->execute(PriceUpdateData::fromArray($payload));

    expect($second->id)->toBe($first->id)
        ->and(PriceHistory::query()->where('product_id', $product->id)->where('store_id', $store->id)->count())->toBe(1);
});

it('rejects a reused trace id with a different payload', function () {
    Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101']);
    $action = new UpdateProductPriceAction;
    $traceId = '550e8400-e29b-41d4-a716-446655440201';

    $action->execute(PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'trace_id' => $traceId,
    ]));

    $action->execute(PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 199,
        'trace_id' => $traceId,
    ]));
})->throws(TraceIdConflictException::class);

it('rejects unknown skus', function () {
    Store::factory()->create(['code' => 'K-101']);

    (new UpdateProductPriceAction)->execute(PriceUpdateData::fromArray([
        'sku' => 'UNKNOWN',
        'store_code' => 'K-101',
        'amount_cents' => 100,
    ]));
})->throws(ModelNotFoundException::class);

it('rejects unknown stores', function () {
    Product::factory()->create(['sku' => 'MILCH-001']);

    (new UpdateProductPriceAction)->execute(PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'UNKNOWN',
        'amount_cents' => 100,
    ]));
})->throws(ModelNotFoundException::class);

it('dispatches a price update job', function () {
    Queue::fake();

    $data = PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 249,
    ]);

    ProcessPriceUpdateJob::dispatch($data);

    Queue::assertPushed(ProcessPriceUpdateJob::class, function (ProcessPriceUpdateJob $job) use ($data): bool {
        return $job->data->sku === $data->sku
            && $job->data->amountCents === 249;
    });
});

it('persists the price when the job runs', function () {
    $product = Product::factory()->create(['sku' => 'MILCH-001']);
    $store = Store::factory()->create(['code' => 'K-101']);

    ProcessPriceUpdateJob::dispatch(PriceUpdateData::fromArray([
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 249,
    ]));

    $this->assertDatabaseHas('price_histories', [
        'product_id' => $product->id,
        'store_id' => $store->id,
        'amount_cents' => 249,
        'currency' => 'EUR',
    ]);
});
