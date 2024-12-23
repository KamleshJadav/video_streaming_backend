<?php
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ActorController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ImageController;

Route::post('categories/add', [CategoryController::class, 'add']); 
Route::post('categories/update', [CategoryController::class, 'update']); 
Route::delete('categories/delete/{id}', [CategoryController::class, 'delete']); 
Route::get('categories/all', [CategoryController::class, 'getAll']); 
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



