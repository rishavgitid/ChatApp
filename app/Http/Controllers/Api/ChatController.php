<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\ChatRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Events\MessageSent;

class ChatController extends Controller
{
    private $chatRepository;
    private $userRepository;

    public function __construct(ChatRepositoryInterface $chatRepository, UserRepositoryInterface $userRepository)
    {
        $this->chatRepository = $chatRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return response()->json($this->userRepository->allUsers(auth()->id()));
    }

    public function messages(Request $request, $userId)
    {
        return response()->json($this->chatRepository->getMessages(auth()->id(), $userId));
    }

    public function send(Request $request)
    {
        $validatedData = $request->validate([
            'receiver_id' => 'required',
            'message' => 'required'
        ]);

        $message = $this->chatRepository->storeMessage([
            'sender_id' => auth()->id(),
            'receiver_id' => $validatedData['receiver_id'],
            'message' => $validatedData['message']
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }

    public function unreadCount()
    {
        $count = \App\Models\Message::where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->count();
        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request)
    {
        $this->chatRepository->markAsRead($request->message_id);
        return response()->json(['status' => 'succcess']);
    }
}
