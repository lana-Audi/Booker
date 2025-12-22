<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    protected $whatsapp;
    protected $otpService;

    public function __construct(WhatsAppService $whatsapp, OtpService $otpService)
    {
        $this->whatsapp = $whatsapp;
        $this->otpService = $otpService;
    }

  
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number'
        ]);

        $otp = $this->otpService->generateOtp($request->phone_number);

        $this->whatsapp->sendMessage(
            $request->phone_number,
            "Your password reset OTP is: $otp"
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'otp' => 'required'
        ]);

        $otpRecord = $this->otpService->verifyOtp(
            $request->phone_number,
            $request->otp
        );

        if (! $otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully'
        ]);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
            'password' => 'required|confirmed'
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
