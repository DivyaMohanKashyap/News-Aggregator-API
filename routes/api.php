<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PreferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Test the API
    Route::get('test', function () {
        return response()->json(['message' => 'API is working']);
    })->name('api.test');

    // Public routes
    Route::post('login', [AuthController::class, 'login'])->name('api.login');
    Route::post('register', [AuthController::class, 'register'])->name('api.register');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('api.forgot-password');
    Route::get('unauthorized', function () {
        return response()->json([
            "status" => false,
            'error' => 'Unauthorized'
        ], 401);
    })->name('api.unauthorized');

    // authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Logout the current user session(s)
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::post('logout-all-devices', [AuthController::class, 'logoutAllDevices'])->name('api.logout-all-devices');

        // Article routes
        Route::get('articles', [ArticleController::class, 'index']);
        Route::post('articles', [ArticleController::class, 'store']);
        Route::get('articles/{id}', [ArticleController::class, 'show']);
        Route::get('articles/search', [ArticleController::class, 'search']);

        // Preference routes
        Route::get('preferences', [PreferenceController::class, 'get']);
        Route::post('preferences', [PreferenceController::class, 'store']);
        Route::get('user/feed', [ArticleController::class, 'personalizedFeed']);
    });
});
