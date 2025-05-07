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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->name('register');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/profile-picture', [UserController::class, 'updateProfilePicture']);

    Route::middleware([CandidateMiddleware::class])->prefix('candidate')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/courses/enrolled', [CourseController::class, 'enrolledCourses']);
        Route::get('/courses/available', [CourseController::class, 'availableCourses']);
        Route::get('/courses/{course}', [CourseController::class, 'showCourse']);
        Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll']);
        Route::get('/courses/{course}/progress', [CourseController::class, 'progress']);
        Route::post('/courses/{course}/progress', [CourseController::class, 'updateEnrollment']);

        Route::get('/quiz/generate', [QuizController::class, 'generateQuiz']);
        Route::post('/quiz/submit', [QuizController::class, 'store']);
        Route::get('/quiz/history', [QuizController::class, 'history']);
        Route::get('/quiz/statistics', [QuizController::class, 'statistics']);
        Route::get('/quiz/results/{attempt}', [QuizController::class, 'results']);
    });



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
