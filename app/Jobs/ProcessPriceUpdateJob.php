<?php

namespace App\Jobs;

use App\Actions\UpdateProductPriceAction;
use App\Data\PriceUpdateData;
use App\Models\PriceHistory;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessPriceUpdateJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 120;

    public function __construct(public PriceUpdateData $data) {}

    public function uniqueId(): string
    {
        return $this->data->traceId;
    }

    public function handle(UpdateProductPriceAction $action): PriceHistory
    {
        return $action->execute($this->data);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('price_update.failed', [
            'trace_id' => $this->data->traceId,
            'sku' => $this->data->sku,
            'store_code' => $this->data->storeCode,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
