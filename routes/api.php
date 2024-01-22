<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Public route
//Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

//Category
Route::get('/category', [CategoryController::class, 'index'])->name('getAllCategory');

//Product
Route::get('/products-by-category/{category_id}', [ProductController::class, 'getProductByCategory'])->name('products.ByCategory');
Route::get('/products', [ProductController::class, 'getAllProducts'])->name('products.index');
Route::get('/product-by-id/{id}', [ProductController::class, 'getProductByID'])->name('product.ByProductID');
//Get image
Route::get('/images/product/{filename}', [ProductController::class, 'getProductImage'])->name('product.image');
//End Public route

//Protected route with Laravel Sanctum
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user-profile', [AuthController::class, 'getUserProfile']);
    Route::get('/check-token', [AuthController::class, 'checkToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/cart', CartController::class)->only('index', 'store');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.delete');
    Route::post('/cart/{action}', [CartController::class, 'decrementOrIncrement'])->name('cart.decrementOrIncrement');
    //Payment 
    Route::post('/charge', [PaymentController::class, 'charge'])->name('payment.charge');
});
Route::group(['middleware' => ['auth:sanctum', 'ability:user:normal']], function () {
    Route::apiResource('/tasks', TaskController::class);
});
//End Protected route with Laravel Sanctum
Route::get('/error', [PaymentController::class, 'error'])->name('payment.error');
Route::get('/success', [PaymentController::class, 'success'])->name('payment.success');
//Protected for admin-site route with Laravel Sanctum

//End Protected for admin-site route with Laravel Sanctum
