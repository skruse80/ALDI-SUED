<?php

namespace App\ViewModels;

use App\Models\Product;
use App\Models\Store;
use App\Queries\CurrentStorePrices;
use App\Support\Money;

final class CurrentPriceRows
{
    public function __construct(
        private readonly CurrentStorePrices $currentStorePrices,
    ) {}

    /**
     * @return list<array{store: string, price: string, recorded_at: string}>
     */
    public function forProduct(Product $product): array
    {
        $rows = [];

        foreach ($this->currentStorePrices->forProduct($product) as $history) {
            $store = $history->store;

            $rows[] = [
                'store' => $store === null ? '—' : $store->code.' · '.$store->name,
                'price' => Money::format($history->amount_cents, $history->currency),
                'recorded_at' => $history->recorded_at->format('d.m.Y H:i'),
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{product: string, price: string, recorded_at: string}>
     */
    public function forStore(Store $store): array
    {
        $rows = [];

        foreach ($this->currentStorePrices->forStore($store) as $history) {
            $product = $history->product;

            $rows[] = [
                'product' => $product === null ? '—' : $product->sku.' · '.$product->name,
                'price' => Money::format($history->amount_cents, $history->currency),
                'recorded_at' => $history->recorded_at->format('d.m.Y H:i'),
            ];
        }

        return $rows;
    }
}
