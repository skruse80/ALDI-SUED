<?php

namespace App\Filament\Resources\PriceHistories\Tables;

use App\Models\PriceHistory;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PriceHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Erfasst')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('store.code')
                    ->label('Filiale')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount_cents')
                    ->label('Preis')
                    ->formatStateUsing(fn (int $state, PriceHistory $record): string => Money::format($state, $record->currency))
                    ->sortable(),
                TextColumn::make('trace_id')
                    ->label('Trace ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                SelectFilter::make('store_id')
                    ->label('Filiale')
                    ->relationship('store', 'code'),
                SelectFilter::make('product_id')
                    ->label('Produkt')
                    ->relationship('product', 'sku'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
