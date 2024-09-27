<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

# Constant
use App\Constants\Constants;

# Custom Helper
use App\Helpers\MyHelper;

# Models
use App\Models\AEToken;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // https://api-sg.aliexpress.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=https://api.fikfis.co.uk/api/webhook&client_id=509370
        // $secretToken = config('services.webhook.secret'); // Store this in your config or .env file
        // $incomingToken = $request->header('X-Webhook-Token');

        // if ($secretToken !== $incomingToken) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        // Log the incoming request for debugging purposes
        Log::info('Webhook received for redirect URL: ', $request->all());

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
        $newAETOken = AETOken::create($request->all());


        // Process the webhook payload
        // For example, you can access the data using $request->input('key')

        // Return a response to acknowledge receipt
        return response()->json(['status' => 'success', 'result' => $request->all()], 200);
    }
}
