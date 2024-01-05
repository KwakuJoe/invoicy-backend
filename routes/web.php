<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



// Route::get('auth/verify-email-view')
// ->name('emailVerifiedView');

Route::get('/404', function () {
   return view('404.404');
})->name('404');

// view route


// generate invoice pdf / download
Route::get('/generate-invoice-pdf/{invoice_id}', [InvoiceController::class, 'generateInvoicePDF'])->name('generateInvoicePDF');
