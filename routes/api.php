<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:api']]);
require base_path('routes/channels.php');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [ChatController::class, 'index']);
    Route::get('/messages/{userId}', [ChatController::class, 'messages']);
    Route::get('/messages/{userId}', [ChatController::class, 'messages']);
    Route::post('/messages', [ChatController::class, 'send']);
    Route::post('/messages/mark-read', [ChatController::class, 'markAsRead']);
    Route::get('/unread-count', [ChatController::class, 'unreadCount']);
});
