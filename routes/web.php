<?php

use App\Http\Controllers\AuthenticationController;
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
Route::get('/auth/verify-email-view/{token}', [AuthenticationController::class, 'verifyEmail'])->name('emailVerifiedView');

//
// Route::controller(PasswordResetController::class)->group(function () {
//     Route::post('/auth/reset-password', 'updatePassword')->name('updatePassword');
//     Route::get('/auth/reset-password/{token}/{email}', 'resetPassword')->name('resetPassword');
// });

Route::post('/auth/reset-password', [PasswordResetController::class,'updatePassword'])->name('updatePassword');
Route::get('/auth/reset-password/{token}/{email}', [PasswordResetController::class,'resetPassword'])->name('resetPassword');

