<?php

namespace App\Services;
use SendGrid\Mail\Mail;

class MailService
{
    public function sendOtp($email, $otp)
    {
        $email = new Mail();
        $email->setFrom("your-email@example.com", "Your App");
        $email->setSubject("Your OTP Code");
        $email->addTo($email);
        $email->addContent("text/plain", "Your OTP is: $otp");

        $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
        try {
            $sendgrid->send($email);
        } catch (\Exception $e) {
            // Handle exception
        }
    }
}