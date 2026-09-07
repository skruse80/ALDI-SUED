<?php

namespace App\Actions;

use App\Models\FailedJob;
use Illuminate\Support\Facades\Artisan;

final class RetryFailedJobAction
{
    public function execute(FailedJob $failedJob): void
    {
        Artisan::call('queue:retry', ['id' => [$failedJob->uuid]]);
    }
}
