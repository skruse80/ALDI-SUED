<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Actions\RecordProductPriceAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_histories_count')
                    ->counts('priceHistories')
                    ->label('Updates')
                    ->sortable(),
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('sku')
            ->recordActions([
                ViewAction::make(),
                RecordProductPriceAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
