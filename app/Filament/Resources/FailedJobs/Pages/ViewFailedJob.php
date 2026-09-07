<?php

namespace App\Filament\Resources\FailedJobs\Pages;

use App\Filament\Actions\RetryFailedJobTableAction;
use App\Filament\Resources\FailedJobs\FailedJobResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFailedJob extends ViewRecord
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RetryFailedJobTableAction::make(),
            DeleteAction::make()
                ->label('Verwerfen'),
        ];
    }
}
