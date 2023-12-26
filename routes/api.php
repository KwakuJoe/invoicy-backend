<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/auth/user', function (Request $request) {
    return $request->user();
});


Route::controller(AuthenticationController::class)->group(function () {
    Route::post('auth/register', 'register');
    Route::post('auth/login', 'login');
    Route::post('auth/update-profile', 'updateProfile');
    Route::get('auth/logout', 'logout')->middleware('auth:sanctum');
    Route::get('auth/send-email-verification/{email}', 'sendEmailVerifcation');
});

Route::controller(PasswordResetController::class)->group(function () {
    Route::post('auth/forget-password', 'forgetPassword');
    Route::get('auth/reset-password/{token}/{expiry}/{email}/', 'resetPassword')->name('resetPassword');
    Route::post('auth/update-password', 'updatePassword');
});

Route::middleware(['auth:sanctum'])->controller(ProductController::class)->group(function () {
    Route::get('/products', 'index');
    Route::get('/product/{id}', 'showProduct');
    Route::post('/create-product', 'createProduct');
    Route::post('/update-product/{id}', 'updateProduct');
    Route::delete('/delete-product/{product_id}', 'deleteProduct');
});

Route::middleware(['auth:sanctum'])->controller(ClientController::class)->group(function () {
    Route::get('/clients', 'index');
    Route::get('/client/{id}', 'showProduct');
    Route::post('/create-client', 'createProduct');
    Route::post('/update-client/{id}', 'updateProduct');
    Route::delete('/delete-client/{client_id}', 'deleteProduct');
});

Route::middleware(['auth:sanctum'])->controller(InvoiceController::class)->group(function () {
    Route::get('/invoices', 'index');
    Route::get('/invoice/{invoice_id}', 'showInvoice');
    Route::post('/create-invoice', 'createInvoice');
    // Route::post('/update-invoice/{invoice_id}', 'updateInvoice');
    Route::put('/update-invoice/{invoice_id}', 'updateInvoice');
    Route::patch('/update-invoice-status/{invoice_id}', 'updateInvoiceStatus');
    Route::delete('/delete-invoice/{invoice_id}', 'deleteInvoice');
});
