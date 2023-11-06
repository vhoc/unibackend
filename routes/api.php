<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EcwidCategoryController;
use App\Http\Controllers\EcwidCouponController;
use App\Http\Controllers\EcwidOrderController;
use App\Http\Controllers\EcwidProductCombinationController;
use App\Http\Controllers\EcwidProductController;
use App\Http\Controllers\EcwidUserController;
use App\Http\Controllers\MlaImageController;
use App\Http\Controllers\MlaProductController;
use App\Http\Controllers\MlaCatalogOptionsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\MlaUserController;
use App\Models\MlaContactMethod;
use App\Http\Controllers\MlaContactMethodController;
use App\Http\Controllers\StripeController;
use App\Models\MlaImage;
use App\Models\MlaProduct;

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
Route::get('/test', function() {
    return 'Holi';
});

// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Do verify email on clicking on the activate button.
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

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

// ECWID USER

// Get user
Route::get('ecwid/customers/{ecwidUserId}', [EcwidUserController::class, 'getById'])-> name('ecwidUser.get');

// CATEGORIES

Route::get('categories', [EcwidCategoryController::class, 'getAll'])->name('categories.getAll');
Route::get('categories/{categoryId}', [EcwidCategoryController::class, 'getOne'])->name('categories.getOne');

// PRODUCTS

Route::get('products/{productId}', [EcwidProductController::class, 'getOne'])->name('product.getOne');
Route::get('products', [EcwidProductController::class, 'search'])->name('products.search');

// PRODUCT COMBINATIONS

Route::get('products/{productId}/combinations', [EcwidProductCombinationController::class, 'getCombinations'])->name('product.getCombinations');

// ORDER

// Calculate
Route::post('ecwid/order/calculate', [EcwidOrderController::class, 'calculate'])->name('order.calculate');

// Create Order
Route::post('ecwid/order', [EcwidOrderController::class, 'create']);

// COUPONS
    
// Find Coupon
Route::get('ecwid/discount_coupons/{code}', [EcwidCouponController::class, 'findCoupon']);

// Validate Coupon
Route::get('ecwid/discount_coupon/{code}', [EcwidCouponController::class, 'validateCoupon']);


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

    // ECWID USER

    // Shipping Address update
    Route::put('ecwid/customers/{ecwidUserId}', [EcwidUserController::class, 'updateShippingAddress']);

    // STRIPE
    Route::get('stripe/countryside', [StripeController::class, 'key']);
    Route::post('stripe/payment-sheet', [StripeController::class, 'paymentSheet']);
    

});


/*
|--------------------------------------------------------------------------
| MLA :: PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::post('/mla/user/register', [MlaUserController::class, 'register']);
Route::post('/mla/login', [AuthController::class, 'login']);
Route::get('/mla/catalog/{hexId}', [MlaUserController::class, 'show']);
Route::get('/mla/catalog/id/{id}', [MlaUserController::class, 'showById']);

// Products
Route::get('/mla/user/{userId}/products', [MlaProductController::class, 'index']);
Route::get('/mla/products/{productHexId}', [MlaProductController::class, 'show']);
Route::get('/mla/products/{productId}', [MlaProductController::class, 'update']);

// Product Images
Route::get('/mla/products/{productId}/images', [MlaImageController::class, 'index']);

// Contact Methods
Route::get('/mla/contactMethods/{userId}', [MlaContactMethodController::class, 'index']);

// Catalog Options
Route::get('/mla/catalogOptions/{userId}', [MlaCatalogOptionsController::class, 'index']);


/*
|--------------------------------------------------------------------------
| MLA :: PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth:sanctum']], function () {

    // User
    // Get one user
    Route::get('/mla/user/{userId}', [AuthController::class, 'show']);

    // Logout
    Route::post('/mla/logout', [AuthController::class, 'logout']);

    // Update user
    Route::put('mla/user', [AuthController::class, 'update']);

    // Update user's password
    Route::put('mla/user/password', [AuthController::class, 'updatePassword']);

    // Delete user
    Route::delete('mla/user', [AuthController::class, 'deleteUser']);

    // Products
    Route::post('/mla/products', [MlaProductController::class, 'store']);
    Route::put('/mla/products', [MlaProductController::class, 'update']);
    Route::delete('mla/products/{productId}', [MlaProductController::class, 'destroy']);

    // Image Files
    Route::post('/mla/images', [MlaImageController::class, 'store']);
    Route::post('/mla/catalog/images', [MlaCatalogOptionsController::class, 'updateImage']);

    // Catalog Options
    Route::put('/mla/catalogOptions', [MlaCatalogOptionsController::class, 'update']);

    // Contact Methods
    Route::post('/mla/contactMethods', [MlaContactMethodController::class, 'store']);
    // Route::put('/mla/contactMethods', [MlaContactMethodController::class, 'update']);
    Route::delete('/mla/contactMethods', [MlaContactMethodController::class, 'destroy']);

});
