<?php

namespace App\Repositories;

use Twilio\Rest\Client;
use App\Models\User;
use App\Interfaces\TwilioRepositoryInterface;

class TwilioRepository implements TwilioRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        // 
    }

    public function callToVerify(User $user)
    {
        $code = random_int(100000, 999999);

        $this->forceFill([
            'verification_code' => $code
        ])->save();

        $client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        $client->calls->create(
            $this->phone,
            "+15306658566", // REPLACE WITH YOUR TWILIO NUMBER
            ["url" => "http://your-ngrok-url>/build-twiml/{$code}"]
        );
    }
}
