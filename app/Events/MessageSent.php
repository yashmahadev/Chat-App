<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message;
    public $room;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // return ['chat'];
        // return new Channel('chat.' . $this->room->room_id);
        // return new PrivateChannel('private-chat.' . $this->room->room_id);
        // return new PrivateChannel('chat.' . $this->room->room_id);
        return new PrivateChannel('chat-room.' . $this->message->room_id);
    }

    public function broadcastAs()
    {
        return 'chat-room';
        // return 'private-chat';
    }

    // public function broadcastWith()
    // {
    //     return ['message' => $this->message->load('sender_id')];
    // }


}
