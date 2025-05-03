<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\MonitorMiddleware;
use App\Http\Middleware\CandidateMiddleware;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCourseController;

use App\Http\Controllers\QuizQuestionController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->name('register');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);



    Route::middleware([AdminMiddleware::class])->group(function () {

        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/admin/recent-registrations', [AdminDashboardController::class, 'recentRegistrations']);
        Route::get('/admin/recent-courses', [AdminDashboardController::class, 'recentCourses']);

        Route::get('/admin/users', [AdminUserController::class, 'index']);
        Route::get('/admin/pending-users', [AdminUserController::class, 'pendingCandidates']);
        Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
        Route::post('/admin/candidate/{id}/approve', [AdminUserController::class, 'approveCondidate']);
        Route::post('/admin/candidate/{id}/reject', [AdminUserController::class, 'rejectCondidate']);
        Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);

        Route::get('/admin/courses', [AdminCourseController::class, 'index']);
        Route::get('/admin/courses/{id}', [AdminCourseController::class, 'show']);
        Route::patch('/admin/courses/{id}/status', [AdminCourseController::class, 'updateStatus']);
        Route::delete('/admin/courses/{id}', [AdminCourseController::class, 'destroy']);

        Route::post('/register-monitor', [AuthController::class, 'monitorRegister'])->name('register-monitor');


        Route::get('/admin/categories', [CategoryController::class, 'index']);
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

    Route::get('/quiz-questions', [QuizQuestionController::class, 'index']);
    Route::post('/quiz-questions', [QuizQuestionController::class, 'store']);
    Route::get('/quiz-questions/{id}', [QuizQuestionController::class, 'show']);
    Route::put('/quiz-questions/{id}', [QuizQuestionController::class, 'update']);
    Route::delete('/quiz-questions/{id}', [QuizQuestionController::class, 'destroy']);
    });
});
