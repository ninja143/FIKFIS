<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // $secretToken = config('services.webhook.secret'); // Store this in your config or .env file
        // $incomingToken = $request->header('X-Webhook-Token');

        // if ($secretToken !== $incomingToken) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        // Log the incoming request for debugging purposes
        Log::info('Webhook received for redirect URL: ', $request->all());

        // Process the webhook payload
        // For example, you can access the data using $request->input('key')

        // Return a response to acknowledge receipt
        return response()->json(['status' => 'success'], 200);
    }
}
