<?php

namespace App\Http\Controllers;

use App\Data\PriceUpdateData;
use App\Http\Requests\StorePriceUpdateRequest;
use App\Jobs\ProcessPriceUpdateJob;
use App\Models\PriceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class IngestPriceUpdateController extends Controller
{
    public function __invoke(StorePriceUpdateRequest $request): JsonResponse
    {
        $payload = $request->safe()->all();
        $payload['amount_cents'] = (int) $payload['amount_cents'];
        $payload['trace_id'] ??= (string) Str::uuid();

        $data = PriceUpdateData::fromArray($payload);

        $existing = PriceHistory::query()->where('trace_id', $data->traceId)->first();

        if ($existing instanceof PriceHistory) {
            if (! $existing->matches($data)) {
                Log::warning('price_update.conflict', [
                    'trace_id' => $data->traceId,
                    'sku' => $data->sku,
                    'store_code' => $data->storeCode,
                ]);

                return response()->json([
                    'message' => 'trace_id already used with a different payload',
                    'trace_id' => $data->traceId,
                ], 409);
            }

            Log::info('price_update.duplicate', [
                'trace_id' => $data->traceId,
                'sku' => $data->sku,
                'store_code' => $data->storeCode,
            ]);

            return response()->json([
                'message' => 'duplicate',
                'trace_id' => $data->traceId,
            ], 202);
        }

        ProcessPriceUpdateJob::dispatch($data);

        Log::info('price_update.accepted', [
            'trace_id' => $data->traceId,
            'sku' => $data->sku,
            'store_code' => $data->storeCode,
        ]);

        return response()->json([
            'message' => 'accepted',
            'trace_id' => $data->traceId,
        ], 202);
    }
}
