<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;

class SmsService
{
    protected $client;
    public $messagingServiceSid; 
    
    public function __construct()
    {
        // Ensure environment variables are set and properly loaded
        $sid = getenv('TWILIO_SID');
        $token = getenv('TWILIO_TOKEN');
        $msgVerifySid = getenv('TWILIO_MSG_SID');
        // echo $sid.' || '.$token.' || '.$msgSid;
        if (!$sid || !$token || !$msgVerifySid) {
            throw new Exception("Twilio SID or Token not set.");
        }

        $this->messagingServiceSid = $msgVerifySid;
        $this->client = new Client($sid, $token);
    }

    public function sendOtp($phoneNumber)
    {
        // return $this->client->messages->create(
        //     $phoneNumber,
        //     [
        //         // 'from' => env('TWILIO_PHONE'),
        //         'messagingServiceSid' => $this->messagingServiceSid,
        //         'body' => `Your FIKFIS SUPPORT verification code is: {$otp}.`
        //     ]
        // );

        // $verification_check = $this->client->verify->v2
        //     ->services($this->messagingServiceSid)
        //     ->verificationChecks->create([
        //         "to" => $phoneNumber,
        //         "code" => "225694",
        //     ]);

        // print $verification_check->status; die;

        $verification = $this->client->verify->v2
            ->services($this->messagingServiceSid)
            ->verifications->create(
                $phoneNumber, // to
                "sms", // channel,
                ['tt' => getenv('OTP_EXPITY_MINUTES')]
            );

        return $verification->status;
    }

    public function verifyOtp($phoneNumber, $otp)
    {
        $verification_check = $this->client->verify->v2
            ->services($this->messagingServiceSid)
            ->verificationChecks->create([
                "to" => $phoneNumber,
                "code" => $otp,
            ]);

        return $verification_check->status;
    }
}