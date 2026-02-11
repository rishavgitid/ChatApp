<?php

namespace App\Repositories;

use App\Interfaces\ChatRepositoryInterface;
use App\Models\Message;

class ChatRepository implements ChatRepositoryInterface
{
    public function getMessages($sender_id, $receiver_id)
    {
        // Mark messages from receiver_id to sender_id (me) as read
        Message::where('sender_id', $receiver_id)
            ->where('receiver_id', $sender_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Message::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $sender_id)
                  ->where('receiver_id', $receiver_id);
        })->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $receiver_id)
                  ->where('receiver_id', $sender_id);
        })->orderBy('created_at', 'asc')->get();
    }

    public function storeMessage(array $data)
    {
        return Message::create($data);
    }

    public function markAsRead($message_id)
    {
        $message = Message::find($message_id);
        if ($message) {
            $message->update(['is_read' => true]);
        }
        return $message;
    }
}
