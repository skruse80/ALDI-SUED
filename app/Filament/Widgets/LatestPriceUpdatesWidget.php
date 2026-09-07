<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestPriceUpdatesWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Letzte Preisupdates')
            ->query(PriceHistory::query()->with(['product', 'store']))
            ->defaultSort('recorded_at', 'desc')
            ->paginated([5])
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Erfasst')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU'),
                TextColumn::make('store.code')
                    ->label('Filiale'),
                TextColumn::make('amount_cents')
                    ->label('Preis')
                    ->formatStateUsing(fn (int $state, PriceHistory $record): string => Money::format($state, $record->currency)),
            ])
            ->recordUrl(fn (PriceHistory $record): ?string => $record->product instanceof Product
                ? ProductResource::getUrl('view', ['record' => $record->product])
                : null);
    }
}
