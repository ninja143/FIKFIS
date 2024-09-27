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
use App\Models\AeToken;

# Validator Rules
// use App\Rules\ValidUsername;

class AliExpressController extends Controller
{
    protected static $ae_url;
    protected static $ae_appkey;
    protected static $ae_appSecret;

    public $accessToken = '';

    // https://api-sg.aliexpress.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=https://api.fikfis.co.uk/api/webhook&client_id=509370
    public function __construct()
    {
        self::$ae_url = env('ALIEXPRESS_API_URL');
        self::$ae_appkey = env('ALIEXPRESS_APPKEY');
        self::$ae_appSecret = env('ALIEXPRESS_APPSECRET');

        $latestRecord = AEToken::latest()->first();
        if (!$latestRecord) {
            dd('Refresh token is expired.');
            // Further logic valid
            // Hitting the code url flow
            // https://api-sg.aliexpress.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=https://api.fikfis.co.uk/api/webhook&client_id=509370
        } else {
            if ($latestRecord->isRefreshTokenExpired()) {
                dd('Refresh token is expired.');
                // Further logic valid
                // Hitting the code url flow
                // https://api-sg.aliexpress.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=https://api.fikfis.co.uk/api/webhook&client_id=509370
            } else {
                if ($latestRecord->isAccessTokenExpired()) {
                    $latestRecord = $this->refreshTokens($latestRecord->refresh_token);
                }
                $this->accessToken = $latestRecord->access_token;
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function generateTokens()
    {
        $apiCode = env('ALIEXPRESS_API_CODE');
        $action = "/auth/token/create";

        $client = new AE_CLIENT(self::$ae_url, self::$ae_appkey, self::$ae_appSecret);
        $request = new AE_REQUEST($action);

        $request->addApiParam("code", $apiCode);

        try {
            // Execute API request, using GOP protocol
            $response = $client->execute($request);
            Log::info("AE Token Request: " . json_encode($request));
            Log::info("AE Token Request: " . json_encode($response));
            $responseData = json_decode($response, true);
            $dataToInsert = [
                'havana_id' => $responseData['havana_id'],
                'user_id' => $responseData['user_id'],
                'user_nick' => $responseData['user_nick'],
                'account_platform' => $responseData['account_platform'],
                'account' => $responseData['account'],
                'locale' => $responseData['locale'],
                'sp' => $responseData['sp'],
                'seller_id' => $responseData['seller_id'],
                'access_token' => $responseData['access_token'],
                'refresh_token' => $responseData['refresh_token'],
                'access_expires_in' => $responseData['expires_in'],
                'refresh_expires_in' => $responseData['refresh_expires_in'],
                'code' => $responseData['code'],
                'request_id' => $responseData['request_id'],
            ];

            // Step 3: Create a new AeToken instance and save it to the database
            if (!AeToken::where('request_id', $responseData['request_id'])->exists()) {
                $aetokenObj = AeToken::create($dataToInsert);
                $resultData = $aetokenObj->only(['access_token', 'refresh_token', 'access_expires_in', 'refresh_expires_in']);
                return response()->json(['message' => 'token inserted', 'result' => $resultData], 200);
            } else {
                Log::info("Token with request_id {$responseData['request_id']} already exists.");
                return response()->json(['message' => 'token is invalid', 'result' => []], 422);
            }
        } catch (Exception $e) {
            // Catch exception and print stack information
            echo $e->getMessage();
        }


    }

    public function refreshTokens($refresh_token)
    {
        $action = "/auth/token/refresh";
        $client = new AE_CLIENT(self::$ae_url, self::$ae_appkey, self::$ae_appSecret);
        $request = new AE_REQUEST($action);
        $request->addApiParam('refresh_token', $refresh_token);

        try {
            // Execute API request, using GOP protocol
            $response = $client->execute($request);
            Log::info("AE Token Refresh Request: " . json_encode($request));
            Log::info("AE Token Refresh Response: " . json_encode($response));
            $responseData = json_decode($response, true);
            $dataToInsert = [
                'havana_id' => $responseData['havana_id'],
                'user_id' => $responseData['user_id'],
                'user_nick' => $responseData['user_nick'],
                'account_platform' => $responseData['account_platform'],
                'account' => $responseData['account'],
                'locale' => $responseData['locale'],
                'sp' => $responseData['sp'],
                'seller_id' => $responseData['seller_id'],
                'access_token' => $responseData['access_token'],
                'refresh_token' => $responseData['refresh_token'],
                'access_expires_in' => $responseData['expires_in'],
                'refresh_expires_in' => $responseData['refresh_expires_in'],
                'code' => $responseData['code'],
                'request_id' => $responseData['request_id'],
            ];

            $aetokenObj = AeToken::create(attributes: $dataToInsert);
            return $dataToInsert;
        } catch (Exception $e) {
            // Catch exception and print stack information
            echo $e->getMessage();
        }


    }

    public function getDsFeedItemIds(Request $request)
    {
        $action = "aliexpress.ds.feed.itemids.get";
        $client = new AE_CLIENT(self::$ae_url, self::$ae_appkey, self::$ae_appSecret);
        $requestApi = new AE_REQUEST($action);
        $requestApi->addApiParam("page_size", "200");
        $requestApi->addApiParam("category_id", "2");
        $requestApi->addApiParam("feed_name", "DS bestseller");
        $requestApi->addApiParam("search_id", "abc123");

        try {
            // Execute API request, using GOP protocol
            $response = $client->execute($requestApi, $this->accessToken);
            Log::info("getDsFeedItemIds Request: " . json_encode($requestApi));
            Log::info(message: "getDsFeedItemIds Response: " . $response);

            // Output the JSON string of the request response result
            return response()->json([
                'message' => json_decode($response)
            ], 200);
        } catch (Exception $e) {
            // Catch exception and print stack information
            Log::error("Error: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function dsTextSearch(Request $request)
    {
        $action = "aliexpress.ds.text.search";
        $client = new AE_CLIENT(self::$ae_url, self::$ae_appkey, self::$ae_appSecret);
        $requestApi = new AE_REQUEST($action);
        $requestApi->addApiParam('keyWord', '\u88D9\u5B50');
        $requestApi->addApiParam('local', 'en');
        $requestApi->addApiParam('countryCode', 'UK');
        $requestApi->addApiParam('categoryId', '18');
        $requestApi->addApiParam('sortBy', 'min_price,asc');
        $requestApi->addApiParam('pageSize', '20');
        $requestApi->addApiParam('pageIndex', '1');
        $requestApi->addApiParam('currency', 'GB');

        try {
            // Execute API request, using GOP protocol
            $response = $client->execute($requestApi, $this->accessToken);
            Log::info("getDsFeedItemIds Request: " . json_encode($requestApi));
            Log::info(message: "getDsFeedItemIds Response: " . $response);

            // Output the JSON string of the request response result
            return response()->json([
                'message' => json_decode($response)
            ], 200);
        } catch (Exception $e) {
            // Catch exception and print stack information
            Log::error("Error: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}
