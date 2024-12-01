<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['prefix' => '/password'], function () {
    Route::post('send-password-reset-email', [AuthController::class, 'sendPasswordResetEmail']);
    Route::post('reset', [AuthController::class, 'passwordReset'])->name('password.reset');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::group(['prefix' => 'articles'], function () {
        Route::get('list', [ArticleController::class, 'lists']);
        Route::get('show/{id}', [ArticleController::class, 'show']);
    });
    Route::group(['prefix' => 'user'], function () {
        Route::get('preferences/get-user-preferences', [UserPreferenceController::class, 'getUserPreferences']);
        Route::post('preferences/set-user-preferences', [UserPreferenceController::class, 'setUserPreference']);
        Route::get('news-feed', [UserPreferenceController::class, 'getNewsFeed']);
    });
});
