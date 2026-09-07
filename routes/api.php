<?php

use App\Http\Controllers\IngestPriceUpdateController;
use Illuminate\Support\Facades\Route;

Route::post('/price-updates', IngestPriceUpdateController::class)
    ->middleware(['throttle:ingest', 'ingest.token']);
