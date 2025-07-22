<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('auth/github/redirect', [SocialAuthController::class, 'redirect']);
Route::get('auth/github/callback', [SocialAuthController::class, 'callback']);
