<?php

use App\Http\Controllers\ShowHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowHomeController::class);
