<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function Store()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|alpha|min:3|max:255',
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'quantity' => 'required|integer|max:500000|min:0',
            'price' => 'required|integer|max:500000|min:0',
            'price_optioin' => 'required|json',
            'description' => 'required|min:3|max:1000',
            'expiry_date' => 'required|date|after:tomorrow',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        $data = json_decode(request()->price_optioin, true);

        $validator = Validator::make($data, [
            "first_period" => 'required|integer|max:365|min:0|gt:second_period',
            'second_period' => 'required|integer|max:364|min:0|lt:first_period',
            "first_percentage" => 'required|numeric|min:0|max:100',
            "second_percentage" => 'required|numeric|min:0|max:100',
            "third_percentage" => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        $current_price = $this->CurrentPrice(json_decode(request()->price_optioin, false), request()->expiry_date, request()->price);

        $name = 'store-id-' . request()->user()->store->id .
            '-product-id-' . (count(request()->user()->store->products) + 1) . '.' . request()->file('image')->extension();

        $image = request()->file('image')->storeAs('storeProducts', $name);

        $attributes = [
            'store_id' => request()->user()->store->id,
            'name' => request()->name,
            'image' => $image,
            'quantity' => request()->quantity,
            'price_optioin' => request()->price_optioin,
            'price' => request()->price,
            'current_price' => $current_price,
            'description' => request()->description,
            'expiry_date' => request()->expiry_date
        ];

        $pro = Product::create($attributes);

        $categoriesId = [];

        request()->categories->toArray();
        foreach(request()->categories as $category){
            if(is_numeric($category) && Category::find($category)){
                array_push($categoriesId, $category);
            }
        }

        if($categoriesId){
            $pro->categories()->attach($categoriesId);
        }

        return response(['status' => 'success', 'message' => 'Product has been created successfully'], 201);
    }

    public function Update()
    {
        $product = Product::find(request()->route('id'));

        if (!$product) {
            return response(['status' => 'failed', 'message' => 'Comment does not exist.'], 404);
        }
        if ($product->store_id != request()->user()->store->id) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|alpha|min:3|max:255',
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'quantity' => 'required|integer|max:500000|min:0',
            'price' => 'required|integer|max:500000|min:0',
            'price_optioin' => 'required|json',
            'description' => 'required|min:3|max:1000'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        $data = json_decode(request()->price_optioin, true);

        $validator = Validator::make($data, [
            "first_period" => 'required|integer|max:365|min:0|gt:second_period',
            'second_period' => 'required|integer|max:364|min:0|lt:first_period',
            "first_percentage" => 'required|numeric|min:0|max:100',
            "second_percentage" => 'required|numeric|min:0|max:100',
            "third_percentage" => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'failed', 'message' => $validator->errors()], 401);
        }

        $current_price = $this->CurrentPrice(json_decode(request()->price_optioin, false), request()->expiry_date, request()->price);

        $name = 'store-id-' . request()->user()->store->id .
            '-product-id-' . (count(request()->user()->store->products) + 1) . '.' . request()->file('image')->extension();

        $image = request()->file('image')->storeAs('storeProducts', $name);


        $product->name = request()->name;
        $product->image = $image;
        $product->quantity = request()->quantity;
        $product->price_optioin = request()->price_optioin;
        $product->price = request()->price;
        $product->current_price = $current_price;
        $product->description = request()->description;
        
        $product->categories()->detach();

        $categoriesId = [];
        
        request()->categories->toArray();
        foreach(request()->categories as $category){
            if(is_numeric($category) && Category::find($category)){
                array_push($categoriesId, $category);
            }
        }

        if($categoriesId){
            $product->categories()->attach($categoriesId);
        }

        $product->save();

        return response(['status' => 'success', 'message' => 'Product has been updated successfully'], 201);
    }

    public function Delete()
    {
        $product = Product::find(request()->route('id'));

        if (!$product) {
            return response(['status' => 'failed', 'message' => 'Comment does not exist.'], 404);
        }
        if ($product->store_id != request()->user()->store->id) {
            return response(['status' => 'failed', 'message' => 'Not Allowed'], 401);
        }

        $product->categories()->detach();
        Product::destroy($product->id);

        return response(['status' => 'success', 'message' => 'Product has been deleted successfully'], 201);
    }
    public function GetProduct(){
        $product = Product::find(request()->route('id'));
        if (!$product) {
            return response(['status' => 'failed', 'message' => 'Comment does not exist.'], 404);
        }
        $attributes = [
            'status' => 'seccuss',
            'data' => $product
        ];
        return response($attributes, 200);
    }
    public function GetProductImage(){
        $product = Product::find(request()->route('id'));
        if (!$product) {
            return response(['status' => 'failed', 'message' => 'Comment does not exist.'], 404);
        }

        $path = storage_path('app\\' . $product->image);

        return response()->download($path);
    }
    private function CurrentPrice($json, $expiries_at, $price)
    {
        $datetime1 = new DateTime(now());
        $datetime2 = new DateTime($expiries_at);
        $remainingDays =  ($datetime1->diff($datetime2))->format('%a');

        if ($remainingDays >= $json->first_period) {
            return ceil($price - ($price * $json->first_percentage) / 100);
        } else if ($remainingDays >= $json->second_period) {
            return ceil($price - ($price * $json->second_percentage) / 100);
        } else {
            return ceil($price - ($price * $json->third_percentage) / 100);
        }
    }
}
