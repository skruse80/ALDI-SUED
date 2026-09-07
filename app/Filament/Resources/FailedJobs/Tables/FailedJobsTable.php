<?php

namespace App\Filament\Resources\FailedJobs\Tables;

use App\Filament\Actions\RetryFailedJobTableAction;
use App\Models\FailedJob;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FailedJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('failed_at')
                    ->label('Fehlgeschlagen')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('queue')
                    ->label('Queue')
                    ->sortable(),
                TextColumn::make('job')
                    ->label('Job')
                    ->state(fn (FailedJob $record): string => $record->displayName())
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('payload', 'like', '%'.$search.'%');
                    }),
                TextColumn::make('exception')
                    ->label('Fehler')
                    ->limit(80)
                    ->wrap()
                    ->tooltip(fn (FailedJob $record): string => $record->exception),
            ])
            ->defaultSort('failed_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                RetryFailedJobTableAction::make(),
                DeleteAction::make()
                    ->label('Verwerfen'),
            ])
            ->toolbarActions([]);
    }
}
