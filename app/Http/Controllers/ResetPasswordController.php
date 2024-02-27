<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassowrdMial;

use App\Models\User;
use App\Models\ResetPassword;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Exception;

class ResetPasswordController extends Controller
{
    public function SendPasswordResetCode()
    {
        $validator = Validator::make(request()->all(), ['email' => 'required|string|max:255|email']);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'message' => $validator->errors()
            ], 401);
        }

        $passReset = ResetPassword::where('email', request()->email)->first();

        if ($passReset && $passReset->number_of_send >= 3) {
            return response([
                'status' => 'failed',
                'message' => 'too many requests, wait 24 hours.'
            ], 429);
        }

        $pin = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            Mail::to(request()->email)->send(new ResetPassowrdMial($pin));
        } catch (Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'Email not sent.'
            ], 503);
        }

        $user = User::where('email', request()->email)->first();

        if ($user)
            $user = $user->id;
        if ($passReset)
            $passReset = $passReset->number_of_send + 1;
        else
            $passReset = 1;

        ResetPassword::updateOrCreate(
            [
                'email' => request()->email
            ],
            [
                'pin_code' => $pin,
                'user_id' => $user,
                'number_of_send' => $passReset
            ]
        );

        return response([
            'status' => 'success',
            'message' => 'Email has been sent.'
        ], 200);
    }

    public function RecivePasswordResetCode()
    {
        $pinCode = ResetPassword::where('pin_code', request()->pin_code)->first();

        if (!$pinCode || $pinCode->email != request()->email) {
            return response([
                'status' => 'failed',
                'message' => 'Invalid PIN code.'
            ], 401);
        }

        $token = $pinCode->user->createToken('Security-Token')->plainTextToken;

        return response([
            'status' => 'success',
            'message' => 'Verified User.',
            'data' => [
                'token' => $token
            ]
        ], 200);
    }

    public function ResetPassword()
    {
        $validator = Validator::make(request()->all(), ['password' => 'required|min:4|max:255']);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        $pinCode = ResetPassword::where('user_id', request()->user()->id)->first();

        if (!$pinCode) {
            return response([
                'status' => 'failed',
                'message' => 'Unauthorized.',
            ], 401);
        }

        ResetPassword::where('user_id', $pinCode->user_id)->delete();

        $user = User::find(request()->user()->id);
        $user->password = Hash::make(request()->password);
        $user->save();

        request()->user()->currentAccessToken()->delete();

        return response(['status' => 'success', 'message' => 'Password has been changed successfully'], 200);
    }
}
