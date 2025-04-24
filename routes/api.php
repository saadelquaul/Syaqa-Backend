<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\MonitorMiddleware;
use App\Http\Middleware\CandidateMiddleware;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CategoryController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->name('register');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);


    //Admin routes
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::post('/register-monitor', [AuthController::class, 'monitorRegister'])->name('register-monitor');
        Route::get('/courses', [CourseController::class, 'index'])->name('getCourses');
        Route::get('/monitor/getCourses', [CourseController::class, 'getCourses'])->name('getCoursesCount');

    });

    Route::middleware([MonitorMiddleware::class])->group(function () {

Route::get('/categories', [CategoryController::class, 'index'])->name('getCategory');
Route::post('/categories', [CategoryController::class, 'store'])->name('addCategory');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('deleteCategory');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('getCategoryById');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('updateCategory');

Route::get('/monitor/courses', [CourseController::class, 'filterByMonitor'])->name('getCourses');
Route::get('/monitor/getCourses', [CourseController::class, 'getCourses'])->name('getCoursesCount');
Route::post('/courses', [CourseController::class, 'store'])->name('addCourse');
Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('deleteCourse');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('getCourseById');
Route::put('/courses/{course}', [CourseController::class, 'update'])->name('updateCourse');
    });
});

