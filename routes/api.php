<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\MonitorMiddleware;
use App\Http\Middleware\CandidateMiddleware;
use App\Http\Controllers\CourseController;
use App\Models\Monitor;

Route::post('/login', [ App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);



    //Admin routes
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::post('/register-monitor', [AuthController::class, 'monitorRegister'])->name('register-monitor');
    });

    Route::middleware([MonitorMiddleware::class])->group(function () {
        Route::get('/upload-course', [CourseController::class, 'store'])->name('addCourse');
        // Route::get('/update-course/{id}', [CourseController::class, 'update'])->name('updateCourse');
        // Route::get('/delete-course/{id}', [CourseController::class, 'destroy'])->name('deleteCourse');
    });
});

