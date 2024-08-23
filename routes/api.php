<?php

use App\Http\Controllers\AutobotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('throttle:api')->group(function () {
    Route::get('/autobots', [AutobotController::class, 'index']);
    Route::get('/autobots/{id}/posts', [AutobotController::class, 'posts'])->where('id', '[0-9]+'); // Fetches posts for a specific Autobot
    Route::get('/posts/{id}/comments', [AutobotController::class, 'comments'])->where('id', '[0-9]+'); // Fetches comments for a specific post

});
