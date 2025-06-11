<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
Broadcast::channel('user.{userId}', function ($user, $userId) {
    Log::info('Authenticating user.{userId}', ['userId' => $userId, 'currentUser' => auth('api')->user()->id ?? 'null']);
    $user = auth('api')->user();
    return $user && (int) $user->id === (int) $userId;
});
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    Log::info('Authorizing chat.{chatId}', ['user_id' => $user->id ?? 'null', 'chatId' => $chatId]);
    return $user && (int) $user->id === 1;
});
