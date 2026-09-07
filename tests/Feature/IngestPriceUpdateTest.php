<?php

use App\Jobs\ProcessPriceUpdateJob;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function ingestHeaders(string $token = 'test-ingest-token'): array
{
    return ['Authorization' => 'Bearer '.$token];
}

it('rejects requests without a bearer token', function () {
    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
    ])->assertUnauthorized();
});

it('rejects requests with an invalid bearer token', function () {
    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
    ], ingestHeaders('wrong-token'))->assertUnauthorized();
});

it('rejects unknown skus at the edge', function () {
    Store::factory()->create(['code' => 'K-101']);

    $this->postJson('/api/price-updates', [
        'sku' => 'UNKNOWN',
        'store_code' => 'K-101',
        'amount_cents' => 129,
    ], ingestHeaders())->assertUnprocessable();
});

it('rejects a negative amount', function () {
    Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101']);

    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => -1,
    ], ingestHeaders())->assertUnprocessable();
});

it('accepts a valid update and dispatches the job', function () {
    Queue::fake();
    Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101']);

    $response = $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'currency' => 'eur',
    ], ingestHeaders());

    $response->assertAccepted()
        ->assertJsonPath('message', 'accepted')
        ->assertJsonStructure(['message', 'trace_id']);

    Queue::assertPushed(ProcessPriceUpdateJob::class, function (ProcessPriceUpdateJob $job): bool {
        return $job->data->sku === 'MILCH-001'
            && $job->data->storeCode === 'K-101'
            && $job->data->amountCents === 129
            && $job->data->currency === 'EUR';
    });
});

it('honours a client-supplied trace id', function () {
    Queue::fake();
    Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101']);
    $traceId = '550e8400-e29b-41d4-a716-446655440000';

    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'trace_id' => $traceId,
    ], ingestHeaders())
        ->assertAccepted()
        ->assertJsonPath('trace_id', $traceId);

    Queue::assertPushed(ProcessPriceUpdateJob::class, fn (ProcessPriceUpdateJob $job): bool => $job->data->traceId === $traceId);
});

it('persists the price when the queue runs inline', function () {
    $product = Product::factory()->create(['sku' => 'MILCH-001']);
    $store = Store::factory()->create(['code' => 'K-101']);

    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 249,
    ], ingestHeaders())->assertAccepted();

    expect(PriceHistory::query()->where('product_id', $product->id)->where('store_id', $store->id)->first())
        ->amount_cents->toBe(249)
        ->currency->toBe('EUR');
});

it('returns duplicate when the same trace id is replayed', function () {
    Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101']);
    $traceId = '550e8400-e29b-41d4-a716-446655440300';
    $payload = [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'trace_id' => $traceId,
    ];

    $this->postJson('/api/price-updates', $payload, ingestHeaders())
        ->assertAccepted()
        ->assertJsonPath('message', 'accepted');

    $this->postJson('/api/price-updates', $payload, ingestHeaders())
        ->assertAccepted()
        ->assertJsonPath('message', 'duplicate')
        ->assertJsonPath('trace_id', $traceId);

    $this->assertDatabaseCount('price_histories', 1);
});

it('rejects a reused trace id with a different payload', function () {
    Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101']);
    $traceId = '550e8400-e29b-41d4-a716-446655440301';

    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'trace_id' => $traceId,
    ], ingestHeaders())->assertAccepted();

    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 199,
        'trace_id' => $traceId,
    ], ingestHeaders())
        ->assertConflict()
        ->assertJsonPath('trace_id', $traceId);

    $this->assertDatabaseCount('price_histories', 1);
});

it('rate limits ingest requests', function () {
    Cache::flush();
    config(['services.ingest.rate_per_minute' => 1]);

    Product::factory()->create(['sku' => 'MILCH-001']);
    Store::factory()->create(['code' => 'K-101']);

    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 129,
        'trace_id' => '550e8400-e29b-41d4-a716-446655440310',
    ], ingestHeaders())->assertAccepted();

    $this->postJson('/api/price-updates', [
        'sku' => 'MILCH-001',
        'store_code' => 'K-101',
        'amount_cents' => 139,
        'trace_id' => '550e8400-e29b-41d4-a716-446655440311',
    ], ingestHeaders())->assertTooManyRequests();

    Cache::flush();
});
