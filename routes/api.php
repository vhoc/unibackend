<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/test', function() {
    return 'Holi';
});

// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Do verify email on clicking on the activate button.
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     // error_log($request);
//     // $request->fulfill();
 
//     // return redirect('/email/verification/successful');
// })->name('verification.verify');

// Unverified e-mail response.
Route::get('/email/verify', function () {
    $response = [
        'message' => 'You must verity your e-mail address.',
    ];

    return response( $response, 401 );
})->middleware('auth')->name('verification.notice');

// Re-send verification email.
Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware(['throttle:10,1'])->name('verification.send');



/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth:sanctum']], function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});
