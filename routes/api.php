<?php

use App\Http\Controllers\Api\Sanctum\SanctumController;
use App\Models\ChatRoom;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/sanctum/register', [SanctumController::class, 'register']); //{"token":"1|zSiG8NBvEF9BYj7h2FcoO5IrKZTfY2TDzy2q1gPf9f03f192"}
Route::post('/sanctum/register', [SanctumController::class, 'register']); //{"token":"1|zSiG8NBvEF9BYj7h2FcoO5IrKZTfY2TDzy2q1gPf9f03f192"}
Route::post('/sanctum/token', [SanctumController::class,'token']); //4|SjWeTY5rhqjD7329F2p6xKKvtB1RojsYLpxr5H4bb843ee8c


Route::get('/chat/{chatRoomId}', function (Request $request, int $chatRoomId) {
    $chatRoom = ChatRoom::query()->findOrFail($chatRoomId);
    $chatRoom->load(['messages.from']);
    $oldMessages = $chatRoom->messages->toArray();
    $user = $request->user();

    return [
        "user_id" => $user->id,
        "chat_room_id" => $chatRoomId,
        "messages" => $oldMessages,
    ];
})->middleware(['auth:sanctum'])->name('api.chat_room');

// Маршрут для аутентификации broadcasting
Route::post('/broadcasting/auth', function (Request $request) {
    $brAuth = Broadcast::auth($request);
    return $brAuth;
})->middleware(['auth:sanctum'])->name('api.broadcasting.auth');

Route::post('/send_message', function (Request $request, ChatService $chatService) {
    $user = $request->user();
    $text = $request->input('message');
    $chatId = $request->input('chat_room_id') ?? 1;
    $chatService->handleNewMessage($chatId, $text, $user);
    return ['success' => true];
})->middleware(['auth:sanctum'])->name('api.send_message');
