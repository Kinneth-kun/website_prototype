<?php

use App\Http\Controllers\Api\AdminResourceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/content/settings', [PublicController::class, 'settings']);
Route::get('/content/{resource}', [PublicController::class, 'index']);
Route::post('/inquiries', [PublicController::class, 'inquiry'])->middleware('throttle:10,1');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/admin/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
Route::post('/admin/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:3,10');
Route::middleware('admin.token')->prefix('admin')->group(function () {
    Route::get('/me', [AuthController::class, 'me']); Route::post('/logout', [AuthController::class, 'logout']); Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:5,10');
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::put('/media/{id}', [MediaController::class, 'update']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);
    Route::get('/{resource}', [AdminResourceController::class, 'index']);
    Route::post('/{resource}', [AdminResourceController::class, 'store']);
    Route::get('/{resource}/{id}', [AdminResourceController::class, 'show']);
    Route::put('/{resource}/{id}', [AdminResourceController::class, 'update']);
    Route::delete('/{resource}/{id}', [AdminResourceController::class, 'destroy']);
});
