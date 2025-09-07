<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $loginRequest)
    {

        $loginRequest->validated();
        if (Auth::attempt($loginRequest->only('email', 'password'))) {
            $loginRequest->session()->regenerate();
            return redirect()->intended('/')->with('success', 'Login successful.');
        }

        return back()->withInput($loginRequest->only('email'))->with('error', 'The provided credentials do not match our records.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }
}
