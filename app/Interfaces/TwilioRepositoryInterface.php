<?php

namespace App\Interfaces;

use App\Models\User;

interface TwilioRepositoryInterface
{
    public function callToVerify(User $user);
}
