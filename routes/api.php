<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\Api\AuthController as ApiAuthCTRL;
use App\Http\Controllers\Api\AliExpressController as AliExpressCTRL;
use App\Http\Controllers\Api\WebhookController as WebhookController;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::apiResource('/player',PlayerController::class);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// // OTP
// // Route::middleware([ApiAuthMiddleware::class])->group(function () {
//   Route::post('check-username', [OtpController::class, 'isUsernameExist'])->name('username.check');
//   Route::post('send-otp', [OtpController::class, 'sendOtp'])->name('otp.send');
//   Route::post('verify-otp', [OtpController::class, 'sendOtp'])->name('otp.verify');
// // });

# Auth Api
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [ApiAuthCTRL::class, 'login'])->withoutMiddleware([EnsureEmailIsVerified::class]);
    Route::post('register-otp', [ApiAuthCTRL::class, 'registerOtp'])->name('register.otp')->name('user.register.send_token');
    Route::post('register', [ApiAuthCTRL::class, 'register'])->name('register')->name('user.register.verify_token');

    Route::group(['middleware' => 'auth:sanctum'], function() {
      Route::get('logout', [ApiAuthCTRL::class, 'logout'])->name('user.logout');
    });
});

# User Routes 
Route::group(['prefix' => 'user'], function () {
  Route::get('profile', [ApiAuthCTRL::class, 'user'])->middleware(['auth:sanctum'])->name('user.profile');
  Route::put('profile', [ApiAuthCTRL::class, 'update'])->middleware(['auth:sanctum'])->name('user.profile.update');
});

// Webhook 
Route::match(['get', 'post'], '/webhook', [WebhookController::class, 'handle']);


# category Routes 
// Route::group(['prefix' => 'category'], function () {
//   Route::get('list', [ProductCategoryCTRL::class, 'index'])->middleware(['auth:sanctum'])->name('category.list');
// });
Route::controller(AliExpressCTRL::class)
  // ->middleware(['auth:sanctum'])
  ->prefix('category')
  ->group(function () {

    // List categories 
    Route::get('list', 'index')->name('category.list');
});


















// Route::post('build-twiml/{code}', 'PhoneVerificationController@buildTwiMl')->name('phoneverification.build');

// Route::get('/email/verify', function () {
//   return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');

// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//   $request->fulfill();

//   return redirect('/home');
// })->middleware(['auth', 'signed'])->name('verification.verify');

// Route::post('/email/verification-notification', function (Request $request) {
//   $request->user()->sendEmailVerificationNotification();

//   return back()->with('message', 'Verification link sent!');
// })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
