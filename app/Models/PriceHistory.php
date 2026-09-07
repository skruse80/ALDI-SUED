<?php

namespace App\Models;

use App\Data\PriceUpdateData;
use Carbon\CarbonImmutable;
use Database\Factories\PriceHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property int $store_id
 * @property int $amount_cents
 * @property string $currency
 * @property CarbonImmutable $recorded_at
 * @property string $trace_id
 * @property-read Product|null $product
 * @property-read Store|null $store
 */
#[Fillable(['product_id', 'store_id', 'amount_cents', 'currency', 'recorded_at', 'trace_id'])]
class PriceHistory extends Model
{
    /** @use HasFactory<PriceHistoryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'recorded_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function matches(PriceUpdateData $data): bool
    {
        $this->loadMissing(['product', 'store']);

        return $this->product?->sku === $data->sku
            && $this->store?->code === $data->storeCode
            && $this->amount_cents === $data->amountCents
            && $this->currency === $data->currency;
    }
}
