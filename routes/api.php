<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EcwidCategoryController;
use App\Http\Controllers\EcwidOrderController;
use App\Http\Controllers\EcwidProductCombinationController;
use App\Http\Controllers\EcwidProductController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\MlaUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
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

// Reset password
Route::post('forgot-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Reset password link lands here
Route::get('forgot-password', function ( Request $request ) {
    $token = $request->query('token');
    $email = $request->query('email');

    return redirect()->route('password.redirect', ['token' => $token, 'email' => $email]);
});

Route::put('forgot-password', [AuthController::class, 'processPasswordReset'])->name('password.processReset');

// CATEGORIES

Route::get('categories', [EcwidCategoryController::class, 'getAll'])->name('categories.getAll');
Route::get('categories/{categoryId}', [EcwidCategoryController::class, 'getOne'])->name('categories.getOne');

// PRODUCTS

Route::get('products/{productId}', [EcwidProductController::class, 'getOne'])->name('product.getOne');
Route::get('products', [EcwidProductController::class, 'search'])->name('products.search');

// PRODUCT COMBINATIONS

Route::get('products/{productId}/combinations', [EcwidProductCombinationController::class, 'getCombinations'])->name('product.getCombinations');

// ORDER

Route::post('order/calculate', [EcwidOrderController::class, 'calculate'])->name('order.calculate');


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

    // Reset password
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    // Update user profile
    Route::put('/user', [AuthController::class, 'updateUser']);

    // Delete user
    Route::delete('/user', [AuthController::class, 'deleteUser']);

});


/*
|--------------------------------------------------------------------------
| MLA :: PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::post('/mla/user/register', [MlaUserController::class, 'register']);