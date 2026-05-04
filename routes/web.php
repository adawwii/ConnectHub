<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/',fn()=> redirect()->route('chat-view'));
Route::get('/user/register',[UserController::class,'show'])->name('register-user')->middleware('guest');
Route::get('/user/login',[UserController::class,'login'])->name('login-user')->middleware('guest');
Route::post('/user/create',[UserController::class,'create'])->name('create-user')->middleware('guest');
Route::post('/user/authenticate',[UserController::class,'authenticate'])->name('authenticate-user')->middleware('guest');
Route::get('/chat/show',[ChatController::class,'index'])->name('chat-view')->middleware('auth');
Route::post('contact/add',[ContactsController::class,'create'])->name('contact-add')->middleware('auth');

// Notifications API (now in ContactsController)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [ContactsController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifications/{id}/accept', [ContactsController::class, 'acceptNotification'])->name('notifications.accept');
    Route::post('/notifications/{id}/reject', [ContactsController::class, 'rejectNotification'])->name('notifications.reject');
    Route::post('chat/send', [MessageController::class, 'sendMessage'])->name('sendMessage');
    Route::put('chat/messages/fallback',[MessageController::class, 'fallbackUpdate'])->name('fallback-messages');
    Route::patch('chat/messages/seen',[MessageController::class, 'messageSeen'])->name('seen-message');
    Route::patch('chat/messages/seen/bulk', [MessageController::class, 'markChatAsSeen'])->name('seen-message-bulk');
    Route::patch('chat/messages/delivered',[MessageController::class, 'messageDelivered'])->name('message-delivered-online');
    Route::get('chat/messages/{friend}', [ChatController::class, 'openChat'])->name('openChat');
});
