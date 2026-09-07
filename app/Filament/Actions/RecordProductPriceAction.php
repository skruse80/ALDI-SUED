<?php

namespace App\Filament\Actions;

use App\Actions\UpdateProductPriceAction;
use App\Data\PriceUpdateData;
use App\Models\Product;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class RecordProductPriceAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'recordPrice';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Preis erfassen')
            ->icon(Heroicon::OutlinedCurrencyEuro)
            ->schema([
                Select::make('store_code')
                    ->label('Filiale')
                    ->options(fn (): array => Store::query()->orderBy('code')->pluck('name', 'code')->all())
                    ->required(),
                TextInput::make('amount_cents')
                    ->label('Preis (Cent)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->helperText('129 bedeutet 1,29 €'),
            ])
            ->action(function (Product $record, array $data): void {
                $storeCode = $data['store_code'] ?? '';
                $amountCents = $data['amount_cents'] ?? null;

                if (! is_string($storeCode) || ! is_numeric($amountCents)) {
                    return;
                }

                app(UpdateProductPriceAction::class)->execute(PriceUpdateData::fromArray([
                    'sku' => $record->sku,
                    'store_code' => $storeCode,
                    'amount_cents' => (int) $amountCents,
                ]));

                Notification::make()
                    ->title('Preisupdate gespeichert')
                    ->success()
                    ->send();
            });
    }
}
