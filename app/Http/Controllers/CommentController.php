<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function Store()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|max:255|string',
            'body' => 'required|string',
            'rate' => 'required|min:0|max:5|numeric'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        $attributes = [
            'user_id' => request()->user()->id,
            'store_id' => request()->store->id,
            'title' => request()->title,
            'body' => request()->body,
            'rate' => request()->rate
        ];

        Comment::create($attributes);

        $this->UpdateStoreRate(request()->rate);

        return response(['status' => 'success', 'message' => 'Comment has been published successfully'], 201);
    }
    public function Update()
    {
        $comment = Comment::find(request()->route('id'));
        if (!$comment) {
            return response(['status' => 'failed', 'message' => 'Comment does not exist.'], 404);
        }
        if ($comment->user->id != request()->user()->id) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        $validator = Validator::make(request()->all(), [
            'title' => 'required|max:255|string',
            'body' => 'required|string',
            'rate' => 'required|min:0|max:5|numeric'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        $comment->title = request()->title;
        $comment->body = request()->body;
        $comment->rate = request()->rate;
        $comment->save();

        $this->UpdateStoreRate(request()->rate);
    }
    public function Delete()
    {
        $comment = Comment::find(request()->route('id'));
        if (!$comment) {
            return response(['status' => 'failed', 'message' => 'Comment does not exist.'], 404);
        }
        if ($comment->user->id != request()->user()->id) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        Comment::destroy($comment->id);

        return response(['status' => 'success', 'message' => 'Comment has been deleted successfully'], 201);
    }

    public function GetProducts()
    {
        $comments = Comment::where('user_id', request()->user()->id)->paginate(35);
        $attributes = [
            'status' => 'success',
        ];
        $attributes = array_merge($attributes, $comments->toArray());
        return response($attributes, 200);
    }

    private function UpdateStoreRate($rate)
    {
        $count = count(Comment::where('store_id', request()->store->id)->get());
        $store = Comment::find(request()->route('id'))->store;
        $store->rate = (($store->rate * ($count - 1)) + $rate) / $count;
        $store->save();
    }
}
