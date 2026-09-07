<?php

namespace App\Filament\Widgets;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PriceUpdateStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $updatesLastDay = PriceHistory::query()
            ->where('recorded_at', '>=', now()->subDay())
            ->count();

        return [
            Stat::make('Preisupdates (24h)', (string) $updatesLastDay)
                ->description('Neue History-Zeilen seit gestern')
                ->icon('heroicon-o-arrow-path'),
            Stat::make('Produkte', (string) Product::query()->count())
                ->icon('heroicon-o-cube'),
            Stat::make('Filialen', (string) Store::query()->count())
                ->icon('heroicon-o-building-storefront'),
        ];
    }
}
