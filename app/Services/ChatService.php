<?php

namespace App\Services;

use App\Events\NewMessage;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;

class ChatService
{
    public function handleNewMessage(int $chatId, string $text, User $user)
    {
        ChatMessage::create([
            'chat_room_id' => $chatId,
            'text' => $text,
            'from_id' => $user->id
        ]);

        NewMessage::dispatch($chatId, $text, $user);
    }

    public function getMessages(int $chatId)
    {
        return ChatRoom::find($chatId)->chatMessages()->orderBy('created_at', 'desc')->get();
    }


}
