<?php

use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ---------------- HomeController ----------------
Route::post('get_language', [HomeController::class, 'get_language']);
Route::post('cast_detail', [HomeController::class, 'cast_detail']);
Route::post('get_category', [HomeController::class, 'get_category']);
Route::post('get_banner', [HomeController::class, 'get_banner']);
Route::post('general_setting', [HomeController::class, 'general_setting']);
Route::post('get_type', [HomeController::class, 'get_type']);
Route::post('get_avatar', [HomeController::class, 'get_avatar']);
Route::post('section_list', [HomeController::class, 'section_list']);
Route::post('section_detail', [HomeController::class, 'section_detail']);
Route::post('add_continue_watching', [HomeController::class, 'add_continue_watching']);
Route::post('remove_continue_watching', [HomeController::class, 'remove_continue_watching']);
Route::post('add_remove_bookmark', [HomeController::class, 'add_remove_bookmark']);
Route::post('add_remove_download', [HomeController::class, 'add_remove_download']);
Route::post('add_transaction', [HomeController::class, 'add_transaction']);
Route::post('add_rent_transaction', [HomeController::class, 'add_rent_transaction']);
Route::post('video_by_category', [HomeController::class, 'video_by_category']);
Route::post('video_by_language', [HomeController::class, 'video_by_language']);
Route::post('get_bookmark_video', [HomeController::class, 'get_bookmark_video']);
Route::post('search_video', [HomeController::class, 'search_video']);
Route::post('user_rent_video_list', [HomeController::class, 'user_rent_video_list']);
Route::post('rent_video_list', [HomeController::class, 'rent_video_list']);
Route::post('get_payment_option', [HomeController::class, 'get_payment_option']);
Route::post('get_video_by_session_id', [HomeController::class, 'get_video_by_session_id']);
Route::post('get_package', [HomeController::class, 'get_package']);
Route::post('get_payment_token', [HomeController::class, 'get_payment_token']);
Route::post('apply_coupon', [HomeController::class, 'apply_coupon']);
Route::post('subscription_list', [HomeController::class, 'subscription_list']);
Route::post('get_pages', [HomeController::class, 'get_pages']);
Route::post('video_view', [HomeController::class, 'video_view']);
Route::post('get_social_link', [HomeController::class, 'get_social_link']);

// ---------------- ChannelController ----------------
Route::post('get_channel', [ChannelController::class, 'get_channel']);
Route::post('channel_section_list', [ChannelController::class, 'channel_section_list']);

// ---------------- UsersController ----------------
Route::post('login', [UserController::class, 'login']);
Route::post('registration', [UserController::class, 'registration']);
Route::post('get_profile', [UserController::class, 'get_profile']);
Route::post('update_profile', [UserController::class, 'update_profile']);
Route::post('image_upload', [UserController::class, 'image_upload']);
Route::post('get_tv_login_code', [UserController::class, 'get_tv_login_code']);
Route::post('tv_login', [UserController::class, 'tv_login']);
