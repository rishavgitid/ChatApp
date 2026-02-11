<?php

namespace App\Interfaces;

interface ChatRepositoryInterface
{
    public function getMessages($sender_id, $receiver_id);
    public function storeMessage(array $data);
    public function markAsRead($message_id);
}
