<?php

namespace App\Filament\Resources\Stores\Schemas;

use App\Models\Store;
use App\ViewModels\CurrentPriceRows;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StoreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label('Filialcode')
                    ->copyable(),
                TextEntry::make('name')
                    ->label('Name'),
                TextEntry::make('uuid')
                    ->label('UUID')
                    ->copyable(),
                RepeatableEntry::make('current_prices')
                    ->label('Aktuelle Produktpreise')
                    ->columnSpanFull()
                    ->state(fn (Store $record): array => app(CurrentPriceRows::class)->forStore($record))
                    ->schema([
                        TextEntry::make('product')->label('Produkt'),
                        TextEntry::make('price')->label('Preis'),
                        TextEntry::make('recorded_at')->label('Erfasst'),
                    ])
                    ->placeholder('Noch keine Preisupdates für diese Filiale.'),
            ]);
    }
}
