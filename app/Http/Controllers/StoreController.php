<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function Store()
    {
        $attributes = [
            'user_id' => request()->user()->id,
            'name' => request()->name,
            'image' => request()->image,
            'phone_number' => request()->phone_number,
            'whatsapp_number' => request()->whatsapp_number,
            'facebook_link' => request()->facebook_link
        ];

        $validator = Validator::make($attributes, [
            'user_id' => 'required|unique:stores',
            'name' => 'required|min:3|max:255|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'phone_number' => 'nullable|numeric|digits:10',
            'whatsapp_number' => 'nullable|numeric|digits:10',
            'facebook_link' => 'nullable|string|url|active_url|regex:/^(https?:\/\/)?(www\.)?facebook.com\/[a-zA-Z0-9(\.\?)?]/'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        if (!request()->phone_number && !request()->whatsapp_number && !request()->facebook_link) {
            return response([
                'status' => 'failed',
                'message' => 'Please provide one contact at least.'
            ], 401);
        }

        $image = null;

        if (request()->image) {
            $name = 'user-id-' . request()->user()->id . '.' . request()->file('image')->extension();
            $image = request()->file('image')->storeAs('storeThumbnail', $name);
        }

        $attributes = [
            'user_id' => request()->user()->id,
            'name' => request()->name,
            'image' => $image,
            'phone_number' => request()->phone_number,
            'whatsapp_number' => request()->whatsapp_number,
            'facebook_link' => request()->facebook_link
        ];

        Store::create($attributes);

        return response(['status' => 'success', 'message' => 'Store has been created successfully'], 201);
    }
    public function Update()
    {
        $user = request()->user();

        if (!Hash::check(request()->password, $user->password)) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        $attributes = [
            'name' => request()->name,
            'image' => request()->image,
            'phone_number' => request()->phone_number,
            'whatsapp_number' => request()->whatsapp_number,
            'facebook_link' => request()->facebook_link
        ];

        $validator = Validator::make($attributes, [
            'name' => 'required|min:3|max:255|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'phone_number' => 'nullable|numeric|digits:10',
            'whatsapp_number' => 'nullable|numeric|digits:10',
            'facebook_link' => 'nullable|string|url|active_url|regex:/^(https?:\/\/)?(www\.)?facebook.com\/[a-zA-Z0-9(\.\?)?]/'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }
        $image = null;

        if (request()->image) {
            $name = 'user-id-' . request()->user()->id . '.' . request()->file('image')->extension();
            $image = request()->file('image')->storeAs('storeThumbnail', $name);
        }

        $store = $user->store;

        $store->name = request()->name;
        $store->image = $image;
        $store->phone_number = request()->phone_number;
        $store->whatsapp_number = request()->whatsapp_number;
        $store->facebook_link = request()->facebook_link;
        $store->save();

        return response(['status' => 'success', 'message' => 'Store has been updated successfully'], 201);
    }

    //Gets functions:
    public function GetThumbnail()
    {
        if (!request()->store->image) {
            return response('', 204);
        }

        $path = storage_path('app\\' . request()->store->image);

        return response()->download($path);
    }
    public function GetInfo()
    {
        $attributes = [
            'status' => 'success',
            'data' => [
                'name' => request()->store->name,
                'phone_number' => request()->store->phone_number,
                'whatsapp_number' => request()->store->whatsapp_number,
                'facebook_link' => request()->store->facebook_link
            ]
        ];
        return response($attributes, 200);
    }
    public function GetRate()
    {
        $attributes = [
            'status' => 'success',
            'data' => [
                'rate' =>  request()->store->rate,
                'comment' => request()->store->comments()->latest()->first()
            ]
        ];
        return response($attributes, 200);
    }

    public function GetComments()
    {
        $comments = Comment::whereNot('user_id', request()->user()->id)->where('store_id', request()->store->id)->paginate(35);
        $attributes = [
            'status' => 'success',
        ];
        if(request()->page == null || request()->page == 1){
            $myComments = Comment::where('user_id', request()->user()->id)->where('store_id', request()->store->id)->get();
            $attributes = [
                'status' => 'success',
                'my_data' => $myComments
            ];
        }
        
        $attributes = array_merge($attributes, $comments->toArray());
        return response($attributes,200);
    }
    public function GetProducts()
    {
        $products = Product::where('store_id', request()->store->id)->paginate(35);
        $attributes = [
            'status' => 'success',
        ];       
        $attributes = array_merge($attributes, $products->toArray());
        return response($attributes,200);
    }
}
