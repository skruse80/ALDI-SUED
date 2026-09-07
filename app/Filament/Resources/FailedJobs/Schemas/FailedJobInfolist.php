<?php

namespace App\Filament\Resources\FailedJobs\Schemas;

use App\Models\FailedJob;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FailedJobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID')
                    ->copyable(),
                TextEntry::make('failed_at')
                    ->label('Fehlgeschlagen')
                    ->dateTime('d.m.Y H:i:s'),
                TextEntry::make('connection')
                    ->label('Connection'),
                TextEntry::make('queue')
                    ->label('Queue'),
                TextEntry::make('job')
                    ->label('Job')
                    ->state(fn (FailedJob $record): string => $record->displayName())
                    ->copyable(),
                TextEntry::make('exception')
                    ->label('Exception')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'whitespace-pre-wrap font-mono text-xs']),
            ]);
    }
}
