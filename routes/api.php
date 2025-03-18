<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', 'App\Http\Controllers\AuthController@logout');
// Route::post('/forgot-password', 'App\Http\Controllers\AuthController@forgotPassword');
// Route::post('/reset-password', 'App\Http\Controllers\AuthController@resetPassword');
// Route::post('/update-password', 'App\Http\Controllers\AuthController@updatePassword');
// Route::post('/update-profile', 'App\Http\Controllers\AuthController@updateProfile');
// Route::post('/update-profile-picture', 'App\Http\Controllers\AuthController@updateProfilePicture');

