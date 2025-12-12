<?php

namespace App\Services;

use App\Models\Otp;

class OtpService
{
    public function generateOtp(string $phone_number)
    {
        $otp = rand(100000, 999999);

        Otp::updateOrCreate(
            ['phone_number' => $phone_number],
            [
                'otp' => $otp,
                'expires_at' => now()->addMinutes(5),
            ]
        );

        return $otp;
    }

    public function verifyOtp(string $phone_number, string $otp)
    {
        return Otp::where('phone_number', $phone_number)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->first();
    }
}
