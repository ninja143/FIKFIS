<?php

namespace App\Http\Controllers;

# App Helpers 
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;


# Constant
use App\Constants\Constants;

# Custom Helper
use App\Helpers\MyHelper;

# Models
use App\Models\Otp;
use App\Models\User;
use App\Models\PasswordResetToken;

# Service Provider
use App\Services\MailService;
use App\Services\SmsService;

# Validator Rules
use App\Rules\ValidUsername;

class OtpController extends Controller
{
    protected  $mailService;
    protected  $smsService;

    public function __construct(MailService $mailService, SmsService $smsService)
    {
        $this->mailService = $mailService;
        $this->smsService = $smsService;

        // $result = $this->myService->performAction();
    }

    public function sendOtp(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate Inputs
            $validator = Validator::make($request->all(), [
                'contact_type' => 'required|in:email,phone',
                'contact_value' => [ 'required', new ValidUsername],
                'username_exist' => 'required|boolean',
                'device_type' => ['nullable', 'string', 'in:ios,android,web,other'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors(),
                ], 422);
            }

            // username check
            if($request->username_exist) {
                $isUsernameExist = $this->isUsernameExist($request->contact_value);
                if (!$isUsernameExist) {
                    return response()->json(['message' => 'Username does not exist in our database'], 400);
                }
            }
            
            // Allowed Logic
            $allowedSending = (($request->username_exist && $isUsernameExist) || (!$request->username_exist)) ? true : false;

            if($allowedSending) {
                // Generate OTP
                $otpArr = MyHelper::generateOtp(0, 6);
                $expiresAt = Carbon::now()->addMinutes(10); // OTP expiry time
                
                // Save OTP
                $resultObj = [
                    // 'otp' => implode("-", $otpArr),
                    'expires_at' => $expiresAt,
                    'contact_type' => $request->contact_type,
                    'contact_value' => $request->contact_value,
                    'device_type' => $request->device_type
                ];

                if ($request->username_exist) {
                    $user = User::where('username', $request->contact_value)->first();
                    // return error if not found 
                    $otp= new Otp([
                        'otp' => '123456',
                        ...$resultObj,
                        'user_id' => $user->id
                    ]);
                } else {
                    $otp= new Otp([
                        'otp' => '123456',
                        ...$resultObj
                    ]);
                }
                
                // Send OTP
                if( $otp->save() ) {
                    DB::commit();

                    $resultObj['verfication_id'] =  MyHelper::encrypt($otp->id. '_'. $request->username_exist);
                    if ($request->contact_type == Constants::CONTACT_TYPE_PHONE) {
                        // $this->smsService->sendOtp( $request->contact_value,  $otp['otp']);
                        return response()->json(['message' => 'OTP sent successfully.', 'result' =>  $resultObj ], 201);
                    } elseif ($request->contact_type == Constants::CONTACT_TYPE_EMAIL) {
                        // $this->mailService->sendOtp( $request->contact_value,  $otp['otp']);
                        return response()->json(['message' => 'OTP sent successfully.', 'result' =>  $resultObj ], 201);
                    } else {
                        return response()->json(['message' => 'OTP couldn\'t sent due to some technical reason, please contact on support.'], 400);
                    }
                    
                } else {
                    return response()->json(['message' => 'OTP can not be sent due to some technical reason, please contact on support.'], 400);
                }
            } else {
                return response()->json(['message' => 'OTP can\'t be sent. Please contact admin.'], 422);
            }
                
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function isUsernameExist($username): bool {
        return User::where('username', $username)->count() > 0 ? true : false;
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'varification_id' => 'required|string|max:256',
            'contact_type' => 'required|in:email,phone',
            'contact_value' => 'required|string',
            'otp' => 'required|string',
        ]);

        // Get otp id and is username check
        [$otp_id, $isUsernameExist] = [...explode('_', MyHelper::decrypt($request->varification_id))];

        $otpRecord = Otp::find($otp_id)
                        ->where('contact_type', $request->contact_type)
                        ->where('contact_value', $request->contact_value)
                        ->where('otp', $request->otp)
                        ->where('invoked', Constants::NOT_INVOKED)
                        ->where('expires_at', '>', Carbon::now())
                        ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        // OTP is valid
        // You may want to delete the OTP record after successful verification
        $otpRecord->invoke();

        return response()->json(['result' => [
            'message' => 'Verified successfully !',
            'verification_id' => $request->varification_id
        ]]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'varification_id' => 'required|string|max:256',
            'contact_type' => 'required|in:email,phone',
            'contact_value' => 'required|string',
            'password' => [
                                'required',
                                'string',
                                'min:8',
                                'max:255',
                                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                                'confirmed'
                            ],
        ]);
        
        // Get otp id and is username check
        [$otp_id, $isUsernameExistCheck] = [...explode('_', MyHelper::decrypt($request->varification_id))];

        $otpRecord = Otp::find($otp_id);

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        // username check
        $isUsernameExist = false; 
        if ($isUsernameExistCheck) {
            $isUsernameExist = $this->isUsernameExist($request->contact_value);
            if (!$isUsernameExist) {
                return response()->json(['message' => 'User record does not exist in our database'], 400);
            }
        }

        if ($isUsernameExistCheck) {
            $user = User::find($otpRecord->user_id)->first();
            // return error if not found 
            $user->updatePassword($request->password);
        } else {
            $user = User::where('username', $request->contact_value)->first();
            // return error if not found 
            $user->updatePassword($request->password);
        }
       
        // OTP invking 
        $otpRecord = Otp::find($otp_id)->delete();

        return response()->json(['message' => 'Password set successfully !'], 400);
    }

    

    public function sendOtp1(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email|unique:users,email|required_without:phone',
            'phone' => 'nullable|regex:/^\+1\d{10}$/|unique:users,phone||required_without:email', // US phone format
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('phone_number', $request->phone)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return $this->sendOtpToUser($user, $request->email, $request->phone);
    }

    private function sendOtpToUser($user, $email, $phone)
    {
        $otp = Str::random(6);

        PasswordResetToken::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'email'],
            ['token' => $otp, 'expires_at' => now()->addMinutes(10)]
        );

        PasswordResetToken::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'phone'],
            ['token' => $otp, 'expires_at' => now()->addMinutes(10)]
        );

        $this->sendEmailOtp($email, $otp);
        $this->sendSmsOtp($phone, $otp);

        return response()->json(['message' => 'OTP sent.']);
    }

    private function sendEmailOtp($email, $otp)
    {
        $emailContent = "Your OTP code is: $otp";

        $email = new SendGridMail();
        $email->setFrom("no-reply@example.com", "Example");
        $email->setSubject("Your OTP Code");
        $email->addTo($email);
        $email->addContent("text/plain", $emailContent);

        $sendGrid = new \SendGrid(env('SENDGRID_API_KEY'));

        try {
            $sendGrid->send($email);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
        }
    }

    private function sendSmsOtp($phone, $otp)
    {
        $twilio = new TwilioClient(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        try {
            $twilio->messages->create(
                $phone,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    'body' => "Your OTP code is: $otp"
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
        }
    }

    public function verifyOtp1(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|phone:US',
            'otp' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('phone_number', $request->phone)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $token = PasswordResetToken::where('user_id', $user->id)
            ->where('token', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$token) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        $token->delete();

        // Generate and return a Sanctum token
        $apiToken = $user->createToken('YourAppName')->plainTextToken;

        return response()->json(['token' => $apiToken]);
    }
}