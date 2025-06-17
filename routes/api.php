<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::options('{any}', function (Request $request) {
    return response()->json([], 200)
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type,Authorization,X-Requested-With')
        ->header('Access-Control-Allow-Credentials', 'true');
})->where('any', '.*');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('jwt')->group(function () {
    Route::get('user', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

use App\Events\PublicMessage;

Route::get('/trigger-public-event', function () {
    broadcast(new PublicMessage('Hello from the server!'))->toOthers();
    return response()->json(['status' => 'event triggered']);
});


use App\Events\ChatMessage;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Auth;

Route::post('/send-chat-message', function (Request $request) {
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $userId = Auth::id(); // or Auth::user()->id
    $chatId = $request->input('chatId');
    $message = $request->input('message');

    broadcast(new ChatMessage($userId, $chatId, $message));
    return response()->json(['status' => 'Message sent']);
})->middleware('jwt'); // Match your JWT middleware


// pusher beam
Route::get('/send-notification', [NotificationController::class, 'sendNoti']);
Route::get('/beams-auth', [NotificationController::class, 'beamsAuth'])->middleware('jwt');


// firebase
Route::get('/send-firebase', [NotificationController::class, 'sendPushNotification']);
Route::post('/subscribe-topic', [NotificationController::class, 'subscribeToTopic']);
