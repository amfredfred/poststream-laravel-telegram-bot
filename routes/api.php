<?php

use App\Http\Controllers\Api\PostEarningController;
use App\Http\Controllers\TelegramUpdateController;
use Illuminate\Support\Facades\Route;

Route::post('/botdate', TelegramUpdateController::class);

Route::prefix('post')->group(function () {
    Route::post('earned', [PostEarningController::class, 'earnPoints']);
    // Add more routes related to posts here
});
