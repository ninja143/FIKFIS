<?php 


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AEToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'havana_id',
        'user_id',
        'user_nick',
        'account_platform',
        'account',
        'locale',
        'sp',
        'seller_id',
        'access_token',
        'refresh_token',
        'expire_time',
        'refresh_expire_in',
        'refresh_token_valid_time',
        'code',
        'request_id',
    ];

    public function isAccessTokenExpired()
    {   
        /*
            // Convert 'expires_in' to seconds
            $expiresInSeconds = $this->expires_in;

            // Calculate the expiration timestamp
            $expirationTimestamp = $this->created_at->timestamp + $expiresInSeconds;

            // Compare with current time
            return now()->timestamp > $expirationTimestamp;
        **/

        // Calculate the expiration timestamp
        $expirationTimestamp = Carbon::createFromTimestamp($this->created_at->timestamp)->addSeconds($this->expires_in)->timestamp;

        // Compare with current time
        return Carbon::now()->timestamp > $expirationTimestamp;
    }

    public function isRefreshTokenExpired()
    {   
        // Calculate the expiration timestamp
        $expirationTimestamp = Carbon::createFromTimestamp($this->created_at->timestamp)->addSeconds($this->refresh_expires_in)->timestamp;

        // Compare with current time
        return Carbon::now()->timestamp > $expirationTimestamp;
    }

    public function isTokenExpired()
    {   
        if($this->isAccessTokenExpired() && $this->isRefreshTokenExpired()) {
            return true;
        } else if(!$this->isAccessTokenExpired() && $this->isRefreshTokenExpired()) {
            return false;
        } else {
            return false;
        }
    }
}