<?php

use App\Http\Controllers\Auth\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongController;

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

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::prefix('songs')->group(function () {
        Route::get('/{limit?}', [SongController::class,'index']);
        Route::get('/detail/{id}', [SongController::class,'show']);
        Route::post('/store', [SongController::class,'store']);
        Route::post('/{id}/upload', [SongController::class,'upload']);
        Route::post('/{id}/download', [SongController::class,'download']);
        Route::post('/{id}/destroy_file', [SongController::class,'destroyFile']);
        Route::put('/{id}', [SongController::class,'update']);
        Route::delete('/{id}', [SongController::class,'destroy']);
        Route::post('/{id}/play', [SongController::class,'play']);
    });
});