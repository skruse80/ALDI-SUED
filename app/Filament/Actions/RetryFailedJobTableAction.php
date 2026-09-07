<?php

namespace App\Filament\Actions;

use App\Actions\RetryFailedJobAction;
use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class RetryFailedJobTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'retryFailedJob';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Erneut versuchen')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Job erneut einreihen?')
            ->modalDescription('Der fehlgeschlagene Job wird zurück auf die Queue gelegt.')
            ->action(function (FailedJob $record): void {
                app(RetryFailedJobAction::class)->execute($record);

                Notification::make()
                    ->title('Job erneut eingereiht')
                    ->success()
                    ->send();
            });
    }
}
