<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

# Models 
use App\Models\User;
use App\Models\PasswordResetToken;

# App Helpers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Exception;

# Constant
use App\Constants\Constants;

# Custom Helper
use App\Helpers\MyHelper;

# Models
use App\Models\Otp;

# Service Provider
use App\Services\MailService;
use App\Services\SmsService;

# Validator Rules
use App\Rules\ValidUsername;

class AuthController extends Controller
{
    protected  $mailService;
    protected  $smsService;

    public function __construct(MailService $mailService, SmsService $smsService)
    {
        $this->mailService = $mailService;
        $this->smsService = $smsService;

        // $result = $this->myService->performAction();
    }

    public function username()
    {
        return ['email', 'phone'];
    }

    public function registerOtp(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate Inputs
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:email,phone',
                'username' => [ 'required', new ValidUsername]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            // username check
            $isUsernameExist = $this->isUsernameExist($request->username);
            if(!$isUsernameExist) {
                // Generate OTP
                $otp = MyHelper::generateOtp( env('OTP_STR_LENGTH', 6),  env('OTP_DIGITS_LENGTH', 6));
                
                // Send OTP
                // if($request->type === Constants::CONTACT_TYPE_EMAIL) {
                //     $result = $this->sendEmailOtp($request->username, $otp, env('OTP_EXPITY_MINUTES', 10));
                // }

                // if($request->type === Constants::CONTACT_TYPE_PHONE) {
                //     $result = $this->sendPhoneOtp($request->username, $otp, env('OTP_EXPITY_MINUTES', 10));
                // }
                $result['status'] = true;

                $setResult = [
                    'username' => $request->username,
                    'expiry_time' => $result['status'] ? env('OTP_EXPITY_MINUTES', 10). ' minutes' : ''
                ];
                if($result['status']) {
                    return response()->json(['message' => 'OTP sent successfully.', 'result' => $setResult ], 201);
                } else {
                    return response()->json(['message' => 'Unable to send otp. please contact admin.', 'result' => $setResult ], 201);
                }
            } else {
                return response()->json([
                    'message' => 'Account alresy exists with this '.($request->type === Constants::CONTACT_TYPE_EMAIL? 'email':'phone'),
                    'result' => $request->input()
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
        
    }

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate Inputs
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:email,phone',
                'username' => ['required', new ValidUsername ],
                'otp' => 'required|string|digits:6',
                'password' => [
                                'required',
                                'string',
                                'min:8',
                                'max:255',
                                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
                            ],
                'device_type' => ['required', 'string', 'in:ios,android,web,other'],
                'device_token' => 'string|max:255',
                'fcm_token' => 'string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            // username check
            $isUsernameExist = $this->isUsernameExist($request->username);
            if(!$isUsernameExist) {
                // Verify OTP
                // if($request->type === Constants::CONTACT_TYPE_EMAIL) {
                //     $result = $this->verifyEmailOtp($request->username, $otp, env('OTP_EXPITY_MINUTES', 10));
                // }

                // if($request->type === Constants::CONTACT_TYPE_PHONE) {
                //     // $result = $this->verifyPhoneOtp($request->username, $otp, env('OTP_EXPITY_MINUTES', 10));
                //     $result = $this->verifyPhoneOtp($request->username, $request->otp);
                // }
                $result['status'] = true;
                $result['message'] = 'approved';
                if($result['status'] == true || 1) {
                    if($result['message'] == 'approved' || 1) {
                        // return response()->json(['message' => 'OTP verified successfully.', ], 201);
                        $user = new User([
                            'username'  => $request->username,
                            'name'  => $request->name,
                            'email' => $request->type === Constants::CONTACT_TYPE_EMAIL ? $request->username : null,
                            'phone' => $request->type === Constants::CONTACT_TYPE_PHONE ? $request->username : null,
                            'password' => Hash::make($request->password),
                            'device_type' => $request->device_type,
                            'device_token' => $request->device_token,
                            'fcm_token' => $request->fcm_token,
                        ]);

                        if($request->type === Constants::CONTACT_TYPE_EMAIL) {
                            $user->email_verified_at = Carbon::now();
                        }

                        if($request->type === Constants::CONTACT_TYPE_PHONE) {
                            $user->phone_verified_at = Carbon::now();
                        }
                        
                        if ($user->save()) {
                            $user->update([ 'remember_token' =>$request->remember_me ]);

                            // $tokenResult = $user->createToken('Personal Access Token For User');
                            $atExpireTime = Carbon::now()->addMinutes(is_numeric(config('sanctum.expiry.access')) ? (int) config('sanctum.expiry.access') : 0);
                            $rtExpireTime = Carbon::now()->addMinutes(is_numeric(config('sanctum.expiry.refresh')) ? (int) config('sanctum.expiry.refresh') : 0);

                            $accessToken = $user->createToken('access_token', [], $atExpireTime);
                            $refreshToken = $user->createToken('refresh_token', [], $rtExpireTime);
                            
                            // Commit the transaction if all operations succeed
                            DB::commit();

                            // Return 
                            return response()->json([
                                'message' => 'Successfully created user !',
                                'result' => [
                                    'profile' => $user->only(['username', 'name', 'email', 'phone']),
                                    'my_wishlist' => [
                                        'products' => [], 
                                        'size' => 10,
                                        'page' => 1,
                                        'sort' => 'product_id'
                                    ],
                                    'recent_orders' => [
                                        'orders' => [], 
                                        'size' => 10,
                                        'page' => 1,
                                        'sort' => 'order_id',
                                    ],
                                    'add_to_carts' => [
                                        'products' => [], 
                                        'size' => 10,
                                        'page' => 1,
                                        'sort' => 'order_id',
                                    ],
                                    'access_token' => $accessToken,
                                    'refresh_token' => $refreshToken->plainTextToken,
                                    'token_type' => 'Bearer',
                                ]
                            ]);
                        } else {
                            return response()->json(['error' => 'Provide unique details'], 400);
                        }
                    } else {
                        return response()->json(['message' => $result['message']], 400);
                    }
                } else {
                    return response()->json(['message' => $result['message']], 400);
                }
                
            } else {
                return response()->json([
                    'message' => 'Account exists with this '.($request->type === Constants::CONTACT_TYPE_EMAIL? 'email':'phone'),
                    'result' => $request->input()
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error: Failed to create user', ['error' => $e->getMessage()]);
            
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
        
    }
    
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     */

    public function login(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'username' => ['required', 'string', new ValidUsername],
                'password' => 'required|string|min:8',
                'device_token' => ['nullable', 'string', 'max:255'],
                'fcm_token' => ['nullable', 'string', 'max:255'],
                'remember_me' => 'boolean'
            ]);

            $credentials = request(['username', 'password']);
            
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Get the authenticated user
            $user = Auth::user();
            // $user = $request->user();

            // Update some fields in the user table
            $user->update([ 'remember_token' =>$request->remember_me, "device_token" => $request->device_token ]);

            // $tokenResult = $user->createToken('Personal Access Token For User');
            $atExpireTime = Carbon::now()->addMinutes(is_numeric(config('sanctum.expiry.access')) ? (int) config('sanctum.expiry.access') : 0);
            $rtExpireTime = Carbon::now()->addMinutes(is_numeric(config('sanctum.expiry.refresh')) ? (int) config('sanctum.expiry.refresh') : 0);

            $accessToken = $user->createToken('access_token', [], $atExpireTime);
            $refreshToken = $user->createToken('refresh_token', [], $rtExpireTime);
            
            DB::commit();
            return response()->json([
                'message' => 'logged-in successfully !',
                'result' => [
                    'profile' => $user->only(['username', 'name', 'email', 'phone', 'profile_picture']),
                    'my_wishlist' => [
                        'products' => [], 
                        'size' => 10,
                        'page' => 1,
                        'sort' => 'product_id'
                    ],
                    'recent_orders' => [
                        'orders' => [], 
                        'size' => 10,
                        'page' => 1,
                        'sort' => 'order_id',
                    ],
                    'add_to_carts' => [
                        'products' => [], 
                        'size' => 10,
                        'page' => 1,
                        'sort' => 'order_id',
                    ],
                    'access_token' => $accessToken->plainTextToken,
                    'refresh_token' => $refreshToken->plainTextToken,
                    'token_type' => 'Bearer'
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('Error: Failed to login', ['error' => $e->getMessage()]);
            
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
            
    }

    /**
        * Get the authenticated User
        *
        * @return [json] user object
    */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'gender' => ['nullable', 'string', 'in:male,female,non-binary,other,prefer_not_to_say'],
            'name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:128'],
            'last_name' => ['nullable', 'string', 'max:128'],
            'device_token' => ['nullable', 'string', 'max:255'],
            'fcm_token' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        // Get the authenticated user
        $user = Auth::user();
        // Update the user details
        if ($request->filled('gender')) {
            $user->gender = $request->input('gender');
        }
        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }
        if ($request->filled('first_name')) {
            $user->first_name = $request->input('first_name');
        }
        if ($request->filled('last_name')) {
            $user->last_name = $request->input('last_name');
        }
        if ($request->filled('device_token')) {
            $user->device_token = $request->input('device_token');
        }
        if ($request->filled('fcm_token')) {
            $user->fcm_token = $request->input('fcm_token');
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    public function updateProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        // Delete the previous profile picture if it exists
        if ($user->profile_picture) {
            $res = Storage::disk('public')->delete('/profile_pictures/'.basename($user->profile_picture));
        }

        // Upload the new profile picture
        $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Update the user's profile picture path
        $user->profile_picture = Storage::url($imagePath);
        // asset(),
        $user->save();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'result' => [
                'profile_picture_url' => $user->profile_picture,
            ]
        ]);
    }

    public function isUsernameExist($username): bool {
        return User::where('username', $username)->count() > 0 ? true : false; 
    }

    /**
        * Logout user (Invoke the token)
        *
        * @return [string] message
    */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);

    }

    private function sendEmailOtp($email, $otp, $expiryMinutes)
    {
        try {
            // OTP Expiry
            $expiresAt = Carbon::now()->addMinutes(is_numeric($expiryMinutes) ? (int) $expiryMinutes : 0); // OTP expiry time
            
            // Save OTP
            $otp = new Otp([
                // 'otp' => implode("-", $otp),
                'otp' => 'AB-123456',
                'expires_at' => $expiresAt,
                'contact_type' => Constants::CONTACT_TYPE_EMAIL,
                'contact_value' => $email,
                'invoked' => Constants::NOT_INVOKED,
            ]);

            // Send OTP
            // Send OTP
            if($otp->save()) {
                $this->mailService->sendOtp($email,  $otp['otp']);
                return [ 'status' => true, 'otp' => $otp ];              
            } else {
                return [ 'status' => false, 'otp' => [] ]; 
            }
        } catch (\Exception $e) {
            return [ 'status' => false, 'message' => $e->getMessage(), 'otp' => [] ]; 
        }
    }

    /**
     * Send Phone OTP
     *
     * @param mixed phone
     * @param mixed otp
     * @param mixed expiryMinutes
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    private function sendPhoneOtp($phone, $otp, $expiryMinutes)
    {
        /*
            DB::beginTransaction();
            try {
                // OTP Expiry
                $expiresAt = Carbon::now()->addMinutes(is_numeric($expiryMinutes) ? (int) $expiryMinutes : 0); // OTP expiry time
                
                // Save OTP
                $otp = new Otp([
                    'otp' => current($otp) === "" ? end($otp) : implode("-", $otp),
                    // 'otp' => 'AB-123456',
                    'expires_at' => $expiresAt,
                    'contact_type' => Constants::CONTACT_TYPE_PHONE,
                    'contact_value' => $phone,
                    'invoked' => Constants::NOT_INVOKED,
                ]);
                
                // Send OTP
                if($otp->save()) {
                    $result = $this->smsService->sendOtp($phone, $otp['otp']);
                    return [ 'status' => true, 'otp' => $otp, 'result' => $result ];              
                } else {
                    return [ 'status' => false, 'message' => 'OTP can not sent due to some technical issue, Please contact on support', 'otp' => [] ]; 
                }
            } catch (\Exception $e) {
                DB::rollback();
                return [ 'status' => false, 'message' => $e->getMessage(), 'otp' => [] ]; 
            }
        */
        
        try {
            // Send OTP
            // $result = $this->smsService->sendOtp($phone);
            return [ 'status' => true, 'message' => "Please enter 123456" ];     
        } catch (Exception $e) { 
            return [ 'status' => false, 'message' => $e->getMessage(), 'otp' => [] ]; 
        }
    }

    private function verifyPhoneOtp($phone, $otp) {
        try {
            // Send OTP
            $result = $this->smsService->verifyOtp($phone, $otp);
            return [ 'status' => true, 'message' => $result ];     
        } catch (Exception $e) {
            return [ 'status' => false, 'message' => $e->getMessage(), 'otp' => [] ]; 
        }
    }

    public function sendPasswordOTP(Request $request){
        DB::beginTransaction();
        try {
            // Validate Inputs
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:email,phone',
                'username' => [ 'required', new ValidUsername]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            // username check
            $isUsernameExist = $this->isUsernameExist($request->username);
            if($isUsernameExist) {
                // Generate OTP
                $otp = MyHelper::generateOtp( env('OTP_STR_LENGTH', 6),  env('OTP_DIGITS_LENGTH', 6));
                
                // Send OTP
                if($request->type === Constants::CONTACT_TYPE_EMAIL) {
                    $result = $this->sendEmailOtp($request->username, $otp, env('OTP_EXPITY_MINUTES', 10));
                }

                if($request->type === Constants::CONTACT_TYPE_PHONE) {
                    $result = $this->sendPhoneOtp($request->username, $otp, env('OTP_EXPITY_MINUTES', 10));
                }

                $setResult = [
                    'username' => $request->username,
                    'expiry_time' => $result['status'] ? env('OTP_EXPITY_MINUTES', 10). ' minutes' : ''
                ];
                if($result['status']) {
                    return response()->json(['message' => 'OTP sent successfully.', 'result' => $setResult ], 201);
                } else {
                    return response()->json(['message' => 'Unable to send otp. please contact admin.', 'result' => $setResult ], 201);
                }
            } else {
                return response()->json([
                    'message' => 'Account does not exist with this '.($request->type === Constants::CONTACT_TYPE_EMAIL? 'email':'phone'),
                    'result' => $request->input()
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request){
        DB::beginTransaction();
        try {
            // Validate Inputs
            $validator = Validator::make($request->all(), [
                'password' => [
                                'required',
                                'string',
                                'min:8',
                                'max:255',
                                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
                            ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }


            $user = Auth::user();
            $user->updatePassword($request->password);

            DB::commit();
            return response()->json(['message' => 'Password changed successfully.', 'result' => [] ], 200);

            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function refreshToken(Request $request) {
        // Validate Inputs
        $validator = Validator::make($request->all(), [
            'refresh_token' => ['required', 'string', 'min:48', 'max:50']
        ]);

        if ($validator->fails()) {
            return response()->json([ 'status' => 'error', 'message' => $validator->errors() ], 422);
        }

        // $token = '3|ozN4wlvYfj0rrtijDzst55ZYX2Lxf1zJXUeQNLjWb0147a94';
        // $tokenInfo = PersonalAccessToken::findToken($token);
        // dd($tokenInfo->expires_at);

        $refreshToken = $request->input('refresh_token');
        $token = PersonalAccessToken::findToken($refreshToken);
        if($token && !$token->expires_at->isPast()) {
            $user = $token->tokenable;

            // Revoke the old refresh token to prevent its reuse
            $token->delete();

            // Create a new refresh token with a new expiry time
            $atExpireTime = Carbon::now()->addMinutes(is_numeric(config('sanctum.expiry.access')) ? (int) config('sanctum.expiry.access') : 0);
            $rtExpireTime = Carbon::now()->addMinutes(is_numeric(config('sanctum.expiry.refresh')) ? (int) config('sanctum.expiry.refresh') : 0);

            $accessToken = $user->createToken('access_token', ["*"], $atExpireTime);
            $newRefreshToken = $user->createToken('refresh_token', ["*"], $rtExpireTime);

            return response()->json([
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $newRefreshToken->plainTextToken,
                'token_type' => 'Bearer',
            ]);
        } else {
            return response()->json([
                'error' => 'Token is expired !',
            ], 422);
        }
        

        return response()->json(['error' => 'Invalid refresh token'], 401);
    }
}
