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
// use App\Models\Otp;

# Service Provider
// use App\Services\MailService;
// use App\Services\SmsService;


# Validator Rules
// use App\Rules\ValidUsername;

class AliExpressController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
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
        $request->addApiParam("code", "GENFIKFIS");

        try {
            // Execute API request, using GOP protocol
            $response = $client->execute($request);
            dd( $response);

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
}
