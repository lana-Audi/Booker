<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'phone_number' => 'required|unique:users,phone_number',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'date_of_birth' => 'required',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'required|string|confirmed',
            ]);

            $profileImageName = null;
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');

                $profileImageName = 'user_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $image->storeAs('public/profile_images', $profileImageName);
            }

            $user = User::create([
                'phone_number' => $validatedData['phone_number'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'password' => Hash::make($validatedData['password']),
                'profile_image' => $profileImageName,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
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
