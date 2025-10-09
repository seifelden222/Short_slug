<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {

            $validated = $request->validated();
            if ($validated['password']) {
                $validated['password'] = Hash::make($validated['password']);
                $user = User::create($validated);
                Auth::login($user);
                return response()->json(['message' => 'Registration successful. Please log in.'], 201);
            } else {
                return response()->json(['message' => 'Password is required.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed. ' . $e->getMessage()], 500);
        }
    }
    public function  login(LoginRequest $request)
    {
        try {
            $validated = $request->validated();

            if (Auth::attempt($validated)) {
              
                $token = $request->user()->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Login successful.',
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $request->user()
                ], 200);
            }

            return response()->json(['message' => 'The provided credentials do not match our records.'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Login failed. ' . $e->getMessage()], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['message' => 'Not authenticated.'], 401);
            }
            // Revoke all tokens for the user (simple logout for API)
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Logout failed. ' . $e->getMessage()], 500);
        }
    }
}
