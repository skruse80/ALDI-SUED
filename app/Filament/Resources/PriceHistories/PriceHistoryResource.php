<?php

namespace App\Filament\Resources\PriceHistories;

use App\Filament\Resources\PriceHistories\Pages\ListPriceHistories;
use App\Filament\Resources\PriceHistories\Tables\PriceHistoriesTable;
use App\Models\PriceHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceHistoryResource extends Resource
{
    protected static ?string $model = PriceHistory::class;

    protected static ?string $modelLabel = 'Preisupdate';

    protected static ?string $pluralModelLabel = 'Preisverlauf';

    protected static ?string $navigationLabel = 'Preisverlauf';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyEuro;

    protected static string|\UnitEnum|null $navigationGroup = 'Preise';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return PriceHistoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceHistories::route('/'),
        ];
    }
}
