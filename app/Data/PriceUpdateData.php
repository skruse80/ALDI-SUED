<?php

namespace App\Data;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class PriceUpdateData
{
    public function __construct(
        public string $sku,
        public string $storeCode,
        public int $amountCents,
        public string $currency,
        public string $traceId,
        public CarbonImmutable $recordedAt,
    ) {
        if ($this->sku === '') {
            throw new InvalidArgumentException('SKU must not be empty.');
        }

        if ($this->storeCode === '') {
            throw new InvalidArgumentException('Store code must not be empty.');
        }

        if ($this->amountCents < 0) {
            throw new InvalidArgumentException('Amount must not be negative.');
        }

        if (preg_match('/^[A-Z]{3}$/', $this->currency) !== 1) {
            throw new InvalidArgumentException('Currency must be a 3-letter ISO code.');
        }

        if (! Str::isUuid($this->traceId)) {
            throw new InvalidArgumentException('Trace ID must be a UUID.');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        if (! array_key_exists('amount_cents', $payload) || ! is_int($payload['amount_cents'])) {
            throw new InvalidArgumentException('Amount cents must be an integer.');
        }

        $sku = $payload['sku'] ?? '';
        $storeCode = $payload['store_code'] ?? '';
        $currency = $payload['currency'] ?? 'EUR';
        $traceId = $payload['trace_id'] ?? (string) Str::uuid();
        $recordedAt = $payload['recorded_at'] ?? CarbonImmutable::now();

        if (! is_string($sku) || ! is_string($storeCode) || ! is_string($currency) || ! is_string($traceId)) {
            throw new InvalidArgumentException('SKU, store code, currency and trace ID must be strings.');
        }

        if (! $recordedAt instanceof CarbonImmutable) {
            if (! is_string($recordedAt)) {
                throw new InvalidArgumentException('recorded_at must be a datetime string.');
            }

            $recordedAt = CarbonImmutable::parse($recordedAt);
        }

        return new self(
            sku: $sku,
            storeCode: $storeCode,
            amountCents: $payload['amount_cents'],
            currency: strtoupper($currency),
            traceId: $traceId,
            recordedAt: $recordedAt,
        );
    }
}
