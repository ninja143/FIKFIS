<?php 

namespace App\Helpers;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

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

    public static function encrypt($data)
    {
        $salt = Str::random(16); // Generate a random salt

        // Prepend and append the salt to the data
        $saltedData = $salt .'|'. $data .'|'. $salt;

        return Crypt::encrypt($saltedData);
    }

    public static function decrypt($encryptedData)
    {
        $decryptedData = Crypt::decrypt($encryptedData);

        // Extract the original data using explode()
        $parts = explode('|', $decryptedData);

        if (count($parts) !== 3) {
            // Invalid format
            throw new InvalidArgumentException('Invalid encrypted data format');
        }

        $salt = $parts[0];
        $originalData = $parts[1];
        $salt2 = $parts[2];

        // Verify that the salts match
        if ($salt !== $salt2) {
            throw new InvalidArgumentException('Invalid encrypted data salt');
        }

        return $originalData;
    }
}


