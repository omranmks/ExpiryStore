<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function test(Request $request){
        return $request->all();
    }
    public function Index(Request $request)
    {
        $user = User::where('email', $request->credential)->orWhere('user_name', $request->credential)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'status' => 'failed',
                'message' => 'These credentials do not match.'
            ], 200);
        }

        $token = $user->createToken('Security-Token')->plainTextToken;

        if ($user->tokens()->count() > 5) {
            $user->tokens()->where('tokenable_id', $user->id)->oldest()->limit(1)->delete();
        }

        $response = [
            'status' => 'success',
            'data' => [
                'name' => $user->name,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'store_id' =>  $user->store->id ?? null,
                'token' => $token
            ]
        ];

        return response($response, 201);
    }
    public function Logout()
    {
        request()->user()->currentAccessToken()->delete();
    }
    public function Store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'user_name' => 'unique:users|required|string|alpha_dash|max:255|min:3',
            'email' => 'unique:users|required|string|max:255|email',
            'password' => 'required|min:4|max:255'
        ]);

        if ($validation->fails()) {
            return response(['status' => 'failed', 'message' => $validation->errors()], 401);
        }

        User::create(request()->all());

        return response(['status' => 'success', 'message' => 'User has been created successfully'], 201);;
    }
    public function Delete()
    {
        $user = request()->user();

        if (!Hash::check(request()->password, $user->password)) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        User::destroy($user->id);

        return response(['status' => 'success', 'message' => 'User has been deleted successfully'], 201);
    }

    public function Update()
    {
        $user = request()->user();

        if (!Hash::check(request()->password, $user->password)) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        $validation = Validator::make(request()->all(), [
            'name' => 'required|string|alpha|max:255',
            'email' => 'required|string|max:255|email|unique:users,email,' . request()->user()->id
        ]);

        if ($validation->fails()) {
            return response([
                'status' => 'failed',
                'message' => $validation->errors()
            ], 401);
        }

        if (request()->email != $user->email) {
            $user->email_verified_at = null;
        }

        $user->name = request()->name;
        $user->email = request()->email;
        $user->save();

        return response(['status' => 'success', 'message' => 'User has been updated successfully'], 201);
    }
    public function ChangePassword()
    {
        $user = request()->user();

        if (!Hash::check(request()->password, $user->password)) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        $validation = Validator::make(request()->all(), [
            'new_password' => 'required|min:4|max:255'
        ]);

        if ($validation->fails()) {
            return response([
                'status' => 'failed',
                'message' => $validation->errors()
            ], 401);
        }

        $user->password = Hash::make(request()->new_password);
        $user->save();

        return response(['status' => 'success', 'message' => 'Password has been updated successfully'], 201);
    }
}
