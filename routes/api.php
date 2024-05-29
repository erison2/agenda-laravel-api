<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('activities', ActivityController::class);
});
