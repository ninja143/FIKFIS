<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

# Constant
use App\Constants\Constants;

class Otp extends Model
{
    protected  $primaryKey = 'id';
    protected $fillable = [
        'otp',
        'expires_at',
        'contact_type',
        'contact_value',
        'user_id',
        'device_type'
    ];

    public function invoke()
    {
        $this->invoked = Constants::INVOKED; // Set the `invoked` status to revoked
        $this->save(); // Save the updated record
    }
}

