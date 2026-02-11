<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function allUsers($exceptId = null)
    {
        if ($exceptId) {
            $users = User::where('id', '!=', $exceptId)->get();
            foreach ($users as $user) {
                // Count unread messages *from* this user *to* the current user (exceptId)
                $user->unread_count = \App\Models\Message::where('sender_id', $user->id)
                    ->where('receiver_id', $exceptId)
                    ->where('is_read', false)
                    ->count();
            }
            return $users;
        }
        return User::all();
    }

    public function find($id)
    {
        return User::findOrFail($id);
    }

    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data)
    {
        return User::create($data);
    }
}
