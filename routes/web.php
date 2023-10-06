<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

// User email verification successful
Route::get('/email/verification/successful', function () {
    return view('success');
})->name('verification.success');

Route::get('/email/verification/failed', function () {
    return view('failed');
})->name('verification.failed');

Route::get('/forgot-password', function ( Request $request ) {
    $token = $request->query('token');
    $email = $request->query('email');

    return view('newpassword', [
        "token" => $token,
        "email" => $email
    ]);
    // return [
    //     "token" => $token,
    //     "email" => $email
    // ];

})->name('password.redirect');