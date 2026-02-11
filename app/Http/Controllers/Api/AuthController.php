<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $validatedData['password'] = Hash::make($request->password);
        $user = $this->userRepository->create($validatedData);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->accessToken
        ], 201);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->token()->revoke();
        }
        return response()->json(['message' => 'Successfully logged out']);
    }
}
