<?php 

namespace App\Helpers;
use Carbon\Carbon;

class MyHelper
{
    public static function generateOtp($strLen = 2, $digitLen = 6): array
    {
        $uppercaseLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';

        // Generate the first 2 uppercase letters
        $chars = substr(str_shuffle($uppercaseLetters), 0, $strLen);

        // Generate the last 6 digits
        $digits = substr(str_shuffle($digits), 0, $digitLen);

        return [$chars, $digits];
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function convertYmdToMdy($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date)->format('m-d-Y');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function convertMdyToYmd($date)
    {
        return Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d');
    }

    function trimAll($string) {
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}


