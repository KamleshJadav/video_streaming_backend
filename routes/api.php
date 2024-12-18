<?php
use App\Http\Controllers\CategoryController;

Route::post('categories/add', [CategoryController::class, 'add']); // Add a category
Route::post('categories/update', [CategoryController::class, 'update']); // Update a category
Route::delete('categories/delete/{id}', [CategoryController::class, 'delete']); // Delete a category
Route::get('categories/all', [CategoryController::class, 'getAll']); // Get all categories (sorted A to Z)
Route::get('categories/paginated', [CategoryController::class, 'getPaginated']); // Get paginated categories (latest first)
Route::get('categories/{id}', [CategoryController::class, 'getById']); // Get category by ID