<?php

namespace App\Http\Controllers;

use App\Mail\VerificationMail;
use App\Models\VerificationCodes;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class VerifyEmailController extends Controller
{
    public function SendVerification()
    {
        if (VerificationCodes::find(request()->user()->id) && count(VerificationCodes::where('user_id', request()->user()->id)->get()) == 5) {
            return response([
                'status' => 'failed',
                'message' => 'too many requests, wait 24 hours.'
            ], 429);
        }

        $pin = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $name = request()->user()->name;

        try {
            Mail::to(request()->user()->email)->send(new VerificationMail($name, $pin));
        } catch (Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'Email has not been sent.'
            ], 503);
        }

        VerificationCodes::create([
            'pin_code' => $pin,
            'user_id' => request()->user()->id
        ]);

        return response([
            'status' => 'success',
            'message' => 'Email has been sent.'
        ], 200);
    }

    public function ReceiveCode()
    {
        if (request()->user()->email_verified_at) {
            return response([
                'status' => 'success',
                'message' => 'Email is already verified.'
            ], 200);
        }

        if (!VerificationCodes::where('user_id', request()->user()->id)->latest()->first()) {
            return response([
                'status' => 'failed',
                'message' => 'Please send verification code again.'
            ], 401);
        }

        $pinCode = VerificationCodes::where('user_id', request()->user()->id)->latest()->first();
        if ($pinCode->pin_code != request()->pinCode) {
            return response([
                'status' => 'failed',
                'message' => 'Invalid PIN code.'
            ], 401);
        }

        VerificationCodes::where('user_id', request()->user()->id)->delete();

        User::where('id', request()->user()->id)->update(['email_verified_at' => now()]);

        return response([
            'status' => 'success',
            'message' => 'Email has been verified.'
        ], 200);
    }
}