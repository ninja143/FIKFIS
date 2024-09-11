<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\OtpController;
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
// Route::middleware([ApiAuthMiddleware::class])->group(function () {
//   Route::post('check-username', [OtpController::class, 'isUsernameExist'])->name('username.check');
  
// });

# Auth Api
Route::group(['prefix' => 'auth'], function () {
    // Route::post('login', [ApiAuthCTRL::class, 'login'])->withoutMiddleware([EnsureEmailIsVerified::class]);
    Route::post('login', [ApiAuthCTRL::class, 'login'])->withoutMiddleware([EnsureEmailIsVerified::class]);
    Route::post('refresh-token', [ApiAuthCTRL::class, 'refreshToken'])->name('token.refresh');
    Route::post('register-otp', [ApiAuthCTRL::class, 'registerOtp'])->name('user.register.otp');
    Route::post('register', [ApiAuthCTRL::class, 'register'])->name('user.register');

    Route::post('forget-password-otp', [OtpController::class, 'sendOtp'])->middleware(['throttle:3,5'])->name('user.forget.password.otp.send');
    Route::post('verify-paasword-otp', [OtpController::class, 'verifyOtp'])->name('user.forget.password.otp.verify');
    Route::post('forget-password', [OtpController::class, 'changePassword'])->name('user.forget.password.reset');

    Route::group(['middleware' => 'auth:sanctum'], function() {
      Route::get('logout', [ApiAuthCTRL::class, 'logout'])->name('user.logout');
    });
});

# User Routes 
Route::group(['prefix' => 'user', 'middleware' => 'auth:sanctum'], function () {
  Route::put('change-password', [ApiAuthCTRL::class, 'changePassword'])->middleware(['throttle:5,1'])->name('user.password.change');
  Route::get('profile', [ApiAuthCTRL::class, 'user'])->name('user.profile');
  Route::put('profile', [ApiAuthCTRL::class, 'update'])->name('user.profile.update');
  Route::post('/profile/picture', [ApiAuthCTRL::class, 'updateProfilePicture'])->name('user.profile.update.picture');
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
