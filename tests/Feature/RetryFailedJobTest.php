<?php

use App\Actions\RetryFailedJobAction;
use App\Models\FailedJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('retries a failed job by uuid', function () {
    $failedJob = FailedJob::factory()->create();

    Artisan::shouldReceive('call')
        ->once()
        ->with('queue:retry', ['id' => [$failedJob->uuid]]);

    app(RetryFailedJobAction::class)->execute($failedJob);
});
