<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\ViewModels\CurrentPriceRows;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sku')
                    ->label('SKU')
                    ->copyable(),
                TextEntry::make('name')
                    ->label('Name'),
                TextEntry::make('uuid')
                    ->label('UUID')
                    ->copyable(),
                RepeatableEntry::make('current_prices')
                    ->label('Aktuelle Filialpreise')
                    ->columnSpanFull()
                    ->state(fn (Product $record): array => app(CurrentPriceRows::class)->forProduct($record))
                    ->schema([
                        TextEntry::make('store')->label('Filiale'),
                        TextEntry::make('price')->label('Preis'),
                        TextEntry::make('recorded_at')->label('Erfasst'),
                    ])
                    ->placeholder('Noch keine Preisupdates für dieses Produkt.'),
            ]);
    }
}
