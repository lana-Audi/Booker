<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use App\Services\WhatsAppService;
use App\Services\OtpService;

class UserController extends Controller
{

    protected $whatsapp;
    protected $otpService;

    public function __construct(WhatsAppService $whatsapp, OtpService $otpService)
    {
        $this->whatsapp = $whatsapp;
        $this->otpService = $otpService;
    }

    public function register(Request $request)
{
    try {
        $validatedData = $request->validate([
            'phone_number' => 'required|unique:users,phone_number',
            'password' => 'required|string|confirmed',
        ]);

        $otp = $this->otpService->generateOtp($validatedData['phone_number']);

        $this->whatsapp->sendMessage(
            $validatedData['phone_number'],
            "Your OTP code is: $otp"
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent. Please verify.',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Registration failed',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//"التحقق من الرقم "واجهة ال 6 ارقام 
public function verifyOtp(Request $request)
{
    $request->validate([
        'phone_number' => 'required',
        'otp' => 'required',
        'password' => 'required|confirmed'
    ]);

    $otpRecord = $this->otpService->verifyOtp($request->phone_number, $request->otp);

    if (! $otpRecord) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }

    $user = User::create([
        'phone_number' => $request->phone_number,
        'password' => Hash::make($request->password),
    ]);

    $otpRecord->delete();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'User verified and registered successfully',
        'token' => $token,
        'user' => $user,
    ]);
}



    //تسجيل الدخول

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'phone_number' => 'required|string',
                'password' => 'required|string',
            ]);

            if (! Auth::attempt($request->only('phone_number', 'password'))) {
                return response()->json([
                    'message' => 'Invalid phone_number or password',
                ], 401);
            }

            $user = User::where('phone_number', $request->phone_number)->FirstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            if (! $user->is_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is pending approval from Admin.',
                ], 403);
            }
            

            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully',
                'data' => [
                    'user' => $user->only(['phone_number', 'first_name', 'last_name', 'date_of_birth']),
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => now()->addDays(7),
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}