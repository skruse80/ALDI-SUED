<?php

namespace Database\Seeders;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ],
        );

        $store = Store::query()->firstOrCreate(
            ['code' => 'K-101'],
            ['name' => 'Köln Süd'],
        );

        $product = Product::query()->firstOrCreate(
            ['sku' => 'MILCH-001'],
            ['name' => 'Vollmilch 1l'],
        );

        PriceHistory::query()->firstOrCreate(
            ['trace_id' => '550e8400-e29b-41d4-a716-446655440100'],
            [
                'product_id' => $product->id,
                'store_id' => $store->id,
                'amount_cents' => 129,
                'currency' => 'EUR',
                'recorded_at' => now()->subHours(2),
            ],
        );

        Store::factory()->count(3)->create();
        Product::factory()->count(8)->create();
    }
}
