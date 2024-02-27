<?php

namespace App\Http\Controllers;


use App\Models\User;

use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAccountsController extends Controller
{
    public function GoogleLogin()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    public function GoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
        } catch (Exception $exception) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid credentials provided.'], 422);
        }
        $emailVerified = null;
        if ($user->user['email_verified'])
            $emailVerified = now();
        $userCreated = User::updateOrCreate(
            [
                'email' => $user->getEmail(),
            ],
            [
                'name' => $user->getName(),
                'google_id' => $user->getId(),
            ]
        );

        $userCreated->email_verified_at = $emailVerified;
        $userCreated->save();

        $token = $userCreated->createToken('Security-Token')->plainTextToken;

        $response = [
            'status' => 'success',
            'data' => [
                'token' => $token
            ]
        ];

        return response($response, 201);
    }
}
