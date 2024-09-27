<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AeToken extends Model
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
        'access_expires_in',
        'refresh_expires_in',
        'code',
        'request_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'access_expires_in' => 'integer',
        'refresh_expires_in' => 'integer',
        'seller_id' => 'integer',
    ];

    public function isAccessTokenExpired()
    {
        $remaining_time_sec = $this->access_expires_in - env('ALIEXPRESS_TOKEN_EARLY_EXPIRY', 1800);
        // Calculate the expiration timestamp
        $expirationTimestamp = Carbon::createFromTimestamp($this->created_at->timestamp)
            ->addSeconds($remaining_time_sec)
            ->timestamp;

        // Compare with current time
        return Carbon::now()->timestamp > $expirationTimestamp;
    }

    public function isRefreshTokenExpired()
    {
        $remaining_time_sec = $this->refresh_expires_in - env('ALIEXPRESS_TOKEN_EARLY_EXPIRY', 1800);
        // Calculate the expiration timestamp
        $expirationTimestamp = Carbon::createFromTimestamp($this->created_at->timestamp)->addSeconds($this->refresh_expires_in)->timestamp;

        // Compare with current time
        return Carbon::now()->timestamp > $expirationTimestamp;
    }

    public function isTokenExpired()
    {
        if ($this->isAccessTokenExpired() && $this->isRefreshTokenExpired()) {
            return true;
        } else if (!$this->isAccessTokenExpired() && $this->isRefreshTokenExpired()) {
            return true;
        } else {
            return false;
        }
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }
}