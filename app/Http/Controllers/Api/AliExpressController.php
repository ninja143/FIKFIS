<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

# App Helpers
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Exception;

use \IopClient as AE_CLIENT;
use \IopRequest as AE_REQUEST;

# Constant
use App\Constants\Constants;

# Custom Helper
use App\Helpers\MyHelper;

# Models
use App\Models\AEToken;

# Service Provider
// use App\Services\MailService;
// use App\Services\SmsService;


# Validator Rules
// use App\Rules\ValidUsername;

class AliExpressController extends Controller
{

    protected  $mailService;
    protected  $smsService;

    // https://api-sg.aliexpress.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=https://api.fikfis.co.uk/api/webhook&client_id=509370
    public function __construct()
    {
        $latestRecord =  AEToken::latest()->first();
        if(!$latestRecord) {
            $this->generateTokens();
            // https://api-sg.aliexpress.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=https://api.fikfis.co.uk/api/webhook&client_id=509370
        } else {
            if ($latestRecord->isTokenExpired()) {
                // Further logic valid
            } else {
                if (!$latestRecord->isAccessTokenExpired && $latestRecord->isRefreshTokenExpired) {
                    $this->generateTokens();
                } else {
                    // Re-authorisation required 
                }
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function generateTokens()
    {   
        // API request address
        $url = env('ALIEXPRESS_API_URL');
        $appkey = env('ALIEXPRESS_APPKEY');
        $appSecret = env('ALIEXPRESS_APPSECRET');
        
        $action = "/auth/token/create";

        // Create an IopClient object and pass in the API address, appkey and appSecret
        $client = new AE_CLIENT($url, $appkey, $appSecret);

        // Create an IopRequest object and pass in the API interface name and parameters
        $request = new AE_REQUEST($action);
        // $request->setApiName($action);
        $request->addApiParam("code", env('ALIEXPRESS_API_CODE'));

        try {
            // Execute API request, using GOP protocol
            $response = $client->execute($request);
            Log::info(`AE Token Request: {$request}`);
            Log::info(`AE Token Response: {$response}`);
            print_r($response);
            $record = AEToken::create($response->all());
            dd($record);
            // {
            //     "refresh_token_valid_time": 1726167798000,
            //     "havana_id": "3001192361513",
            //     "expire_time": 1726081398000,
            //     "locale": "zh_CN",
            //     "user_nick": "uk3399472517mhmae",
            //     "access_token": "50000700c14umY9mwordcyIwlp1ec42992yZyojXeEUdkzxfKmSk0gut9ikjP0OPHAZs",
            //     "refresh_token": "50001701414dEpfsdjrdvoFZki1cd65cb9y3rr0diHyGvluvol5CtreKo1dMN7jAHq0h",
            //     "user_id": "6072325717",
            //     "account_platform": "buyerApp",
            //     "refresh_expires_in": 172800,
            //     "expires_in": 86400,
            //     "sp": "ae",
            //     "seller_id": "6072325717",
            //     "account": "fikfis.co.uk@gmail.com",
            //     "code": "0",
            //     "request_id": "2101289517259949988748367"
            // }

            // Save this into the table
            // Set cron for an hour that will check the expiry of the token 
            // Get the new token again using refresh token and update in db
            // Refresh token expiry will not be extended 
            // Authorisation process will be repeated again to get the code 
            // Once it is expired , Show in panel somewhere and automate the link in the same way 

            //-- Authorisation link 
            // 

            // Output the JSON string of the request response result
            return response()->json([
                'message' => json_decode($response)
            ], 200);
            // echo json_encode($response);

            // Output the GOP format string of the request response result
            // echo $response->getGopResponseBody();
        } catch (Exception $e) {
            // Catch exception and print stack information
            echo $e->getMessage();
        }

        
    }

    public static function generateAccessToken(){

    }
}
