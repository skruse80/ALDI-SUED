<?php

namespace App\Filament\Resources\Stores\RelationManagers;

use App\Models\PriceHistory;
use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'priceHistories';

    protected static ?string $title = 'Preisverlauf';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
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
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
