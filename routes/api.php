<?php
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ActorController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BannerController;
use App\Http\Middleware\JwtMiddleware;


Route::post('user/login', [UserController::class, 'login']); 
Route::post('user/register', [UserController::class, 'register']); 
Route::get('user/check-admin', [UserController::class, 'checkAdmin']); 
Route::get('user/paginated', [UserController::class, 'getPaginated']);  

// Route::middleware([JwtMiddleware::class])->group(function () {

Route::post('categories/add', [CategoryController::class, 'add']); 
Route::post('categories/update', [CategoryController::class, 'update']); 
Route::delete('categories/delete/{id}', [CategoryController::class, 'delete']); 
Route::get('categories/all', [CategoryController::class, 'getAll']);  // used in Mobile 
Route::get('categories/paginated', [CategoryController::class, 'getPaginated']); 
Route::get('categories/{id}', [CategoryController::class, 'getById']); 

Route::post('actors/add', [ActorController::class, 'add']); 
Route::post('actors/update', [ActorController::class, 'update']); 
Route::delete('actors/delete/{id}', [ActorController::class, 'delete']); 
Route::get('actors/all', [ActorController::class, 'getAll']); 
Route::get('actors/paginated', [ActorController::class, 'getPaginated']); 
Route::get('actors/{id}', [ActorController::class, 'getById']); 

Route::post('channels/add', [ChannelController::class, 'add']); 
Route::post('channels/update', [ChannelController::class, 'update']); 
Route::delete('channels/delete/{id}', [ChannelController::class, 'delete']); 
Route::get('channels/all', [ChannelController::class, 'getAll']); 
Route::get('channels/paginated', [ChannelController::class, 'getPaginated']); 
Route::get('channels/{id}', [ChannelController::class, 'getById']); 

Route::post('videos/add', [VideoController::class, 'add']); 
Route::post('videos/update', [VideoController::class, 'update']); 
Route::delete('videos/delete/{id}', [VideoController::class, 'delete']); 
Route::get('videos/all', [VideoController::class, 'getAll']); 
Route::get('videos/paginated', [VideoController::class, 'getPaginated']); 
Route::get('videos/{id}', [VideoController::class, 'getById']); 

Route::post('images/add', [ImageController::class, 'add']); 
Route::post('images/update', [ImageController::class, 'update']); 
Route::delete('images/delete/{id}', [ImageController::class, 'delete']); 
Route::get('images/all', [ImageController::class, 'getAll']); 
Route::get('images/paginated', [ImageController::class, 'getPaginated']); 
Route::get('images/{id}', [ImageController::class, 'getById']); 

Route::post('user/update', [UserController::class, 'update']); 
Route::post('user/change-password', [UserController::class, 'changePassword']); 

Route::post('banner/add', [BannerController::class, 'add']); 
Route::post('banner/update', [BannerController::class, 'update']); 
Route::delete('banner/delete/{id}', [BannerController::class, 'delete']); 
Route::get('banner/all', [BannerController::class, 'getAll']); 
Route::get('banner/paginated', [BannerController::class, 'getPaginated']); 
Route::get('banner/{id}', [BannerController::class, 'getById']); 
Route::get('banner/update-status', [BannerController::class, 'updateStatus']); 
Route::get('banner/get-active-banner', [BannerController::class, 'getAllActiveBanner']); 

Route::post('notification/add', [NotificationController::class, 'add']); 
Route::post('notification/add-all-user', [NotificationController::class, 'addAllUser']); 
Route::post('notification/update', [NotificationController::class, 'update']); 
Route::delete('notification/delete/{id}', [NotificationController::class, 'delete']); 
Route::get('notification/all', [NotificationController::class, 'getAll']); 
Route::get('notification/paginated', [NotificationController::class, 'getPaginated']); 
Route::get('notification/{id}', [NotificationController::class, 'getById']); 

// });


// used in mobile 
Route::post('mobile/wishlist/add', [WishlistController::class, 'add']); 
Route::post('mobile/wishlist/remove', [WishlistController::class, 'remove']); 
Route::post('mobile/wishlist/toggle', [WishlistController::class, 'toggle']); 
Route::get('mobile/wishlist/get-paginated', [WishlistController::class, 'getPaginated']); 
Route::post('mobile/wishlist/get-all', [WishlistController::class, 'getAll']); 

Route::get('mobile/categories/all', [CategoryController::class, 'getAll']); 
Route::get('mobile/videos/popular', [VideoController::class, 'getPopular']); 
Route::get('mobile/videos/tradding', [VideoController::class, 'getTradding']); 
Route::get('mobile/videos/get-mobile-paginated', [VideoController::class, 'getMobilePaginated']); 
Route::get('mobile/videos/get-by-id-mobile', [VideoController::class, 'getByIdMobile']); 
Route::get('mobile/notification/user-all-notification', [NotificationController::class, 'userAllNotification']); 