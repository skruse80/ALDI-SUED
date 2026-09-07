<?php

namespace App\Queries;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class CurrentStorePrices
{
    /**
     * Latest accepted price per store for one product.
     *
     * @return Collection<int, PriceHistory>
     */
    public function forProduct(Product $product): Collection
    {
        return $this->latest(
            groupColumn: 'store_id',
            constrain: fn (Builder $query): Builder => $query->where('product_id', $product->id),
        )->with('store')->get();
    }

    /**
     * Latest accepted price per product for one store.
     *
     * @return Collection<int, PriceHistory>
     */
    public function forStore(Store $store): Collection
    {
        return $this->latest(
            groupColumn: 'product_id',
            constrain: fn (Builder $query): Builder => $query->where('store_id', $store->id),
        )->with('product')->get();
    }

    /**
     * @param  'store_id'|'product_id'  $groupColumn
     * @param  callable(Builder<PriceHistory>): Builder<PriceHistory>  $constrain
     * @return Builder<PriceHistory>
     */
    private function latest(string $groupColumn, callable $constrain): Builder
    {
        $rankSql = match ($groupColumn) {
            'store_id' => 'ROW_NUMBER() OVER (PARTITION BY store_id ORDER BY recorded_at DESC, id DESC) as price_rank',
            'product_id' => 'ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY recorded_at DESC, id DESC) as price_rank',
        };

        $ranked = PriceHistory::query()
            ->select('price_histories.*')
            ->selectRaw($rankSql);

        $ranked = $constrain($ranked);

        return PriceHistory::query()
            ->fromSub($ranked, 'price_histories')
            ->where('price_rank', 1);
    }
}
