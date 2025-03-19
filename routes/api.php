<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminMiddleware;



Route::post('/login', [ App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);


    //Admin routes
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::post('/register-monitor', [AuthController::class, 'monitorRegister']);
    });
});

