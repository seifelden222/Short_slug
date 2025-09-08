<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $registerRequest)
    {

        $validated = $registerRequest->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        Auth::login($user);
        return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
    }
}
