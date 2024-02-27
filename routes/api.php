<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\SocialAccountsController;
use App\Http\Controllers\StoreController;


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('test', [UserController::class, 'test']);
    //User APIs
    Route::post('user/logout', [UserController::class, 'Logout']);
    Route::delete('user/delete', [UserController::class, 'Delete']);
    Route::patch('user/update', [UserController::class, 'Update']);
    Route::patch('user/changepassword', [UserController::class, 'ChangePassword']);
    Route::get('user/info', [UserController::class, 'GetUser']);
    
    //Search API
    Route::get('/', [SearchController::class, 'GetSearch']);
    
    //Store APIs
    Route::put('store/create', [StoreController::class, 'Store']);


    Route::group(['middleware' => 'hasStore'], function () {

        //Store APIs
        Route::patch('store/update', [StoreController::class, 'Update']);

        //Product APIs
        Route::put('product/create', [ProductController::class, 'Store']);
        Route::patch('product/{id}/update', [ProductController::class, 'Update']);
        Route::delete('product/{id}/delete', [ProductController::class, 'Delete']);
        Route::get('product/{id}/image', [ProductController::class, 'GetProductImage']);
        Route::get('product/{id}', [ProductController::class, 'GetProduct']);

        Route::group(['middleware' => 'storeExist'], function () {

            //Store APIs
            Route::get('store/{id}/thumbnail', [StoreController::class, 'GetThumbnail']);
            Route::get('store/{id}/info', [StoreController::class, 'GetInfo']);
            Route::get('store/{id}/rate', [StoreController::class, 'GetRate']);
            Route::get('store/{id}/comments', [StoreController::class, 'GetComments']);
            Route::get('store/{id}/products', [StoreController::class, 'GetProducts']);

            //Comment APIs
            Route::put('store/{id}/comment/create', [CommentController::class, 'Store']);
        });
    });

    //Comment APIs
    Route::patch('comment/update/{id}', [CommentController::class, 'Update']);
    Route::delete('comment/delete/{id}', [CommentController::class, 'Delete']);
    Route::get('comment/get', [CommentController::class, 'Get']);

    //Emails APIs
    Route::post('email/send-verify-code', [VerifyEmailController::class, 'SendVerification']);
    Route::post('email/recive-verify-code', [VerifyEmailController::class, 'ReceiveCode']);

    //Password APIs
    Route::patch('password/reset', [ResetPasswordController::class, 'ResetPassword']);
});

//User APIs
Route::put('user/create', [UserController::class, 'store']);
Route::post('user/login', [UserController::class, 'index']);

//Social User APIs
Route::get('social/google', [SocialAccountsController::class, 'GoogleLogin']);
Route::get('social/google/callback', [SocialAccountsController::class, 'GoogleCallback']);

//Password APIs
Route::post('password/send-code', [ResetPasswordController::class, 'SendPasswordResetCode']);
Route::post('password/recive-code', [ResetPasswordController::class, 'RecivePasswordResetCode']);

