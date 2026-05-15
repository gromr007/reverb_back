<?php

namespace App\Listeners;

use App\Events\NewMessage;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;

class NewMessageFromClient
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ChannelManager $channelManager,
        protected ChatService  $chatService,
    )
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageReceived $event): void
    {

        try {
            $message = json_decode($event->message, associative: true, flags: JSON_THROW_ON_ERROR);

            $eventName = $message['event'];

            if(($eventName !== 'NewMessageFromClient') && ($eventName !== 'client-NewMessageFromClient')){
                return;
            }

            $payload = $message['data'];
            $chatRoomId = $payload['chat_room_id'];
            $message = $payload['message'];
            $connections = $this->channelManager->connections('presence-chat.' . $chatRoomId);
            $myConnection = $connections[$event->connection->id()];
            $myConnectionData = $myConnection->data();
            $userId = $myConnectionData['user_id'];
            $user = User::find($userId);

            $this->chatService->handleNewMessage($chatRoomId, $message, $user);
            logger()->debug('message', $message);
        } catch (\Throwable $th) {
            logger()->error($th);
        }
    }
}
