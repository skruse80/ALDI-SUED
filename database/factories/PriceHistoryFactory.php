<?php

namespace Database\Factories;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceHistory>
 */
class PriceHistoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'store_id' => Store::factory(),
            'amount_cents' => fake()->numberBetween(1, 9_999),
            'currency' => 'EUR',
            'recorded_at' => now(),
            'trace_id' => fake()->uuid(),
        ];
    }
}
