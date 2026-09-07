<?php

namespace App\Actions;

use App\Data\PriceUpdateData;
use App\Exceptions\TraceIdConflictException;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\UniqueConstraintViolationException;

final class UpdateProductPriceAction
{
    public function execute(PriceUpdateData $data): PriceHistory
    {
        $existing = PriceHistory::query()->where('trace_id', $data->traceId)->first();

        if ($existing instanceof PriceHistory) {
            $this->guardAgainstConflict($existing, $data);

            return $existing;
        }

        $product = Product::query()->where('sku', $data->sku)->firstOrFail();
        $store = Store::query()->where('code', $data->storeCode)->firstOrFail();

        try {
            return PriceHistory::query()->create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'amount_cents' => $data->amountCents,
                'currency' => $data->currency,
                'recorded_at' => $data->recordedAt,
                'trace_id' => $data->traceId,
            ]);
        } catch (UniqueConstraintViolationException) {
            $existing = PriceHistory::query()->where('trace_id', $data->traceId)->firstOrFail();
            $this->guardAgainstConflict($existing, $data);

            return $existing;
        }
    }

    private function guardAgainstConflict(PriceHistory $existing, PriceUpdateData $data): void
    {
        if (! $existing->matches($data)) {
            throw new TraceIdConflictException($data->traceId);
        }
    }
}
