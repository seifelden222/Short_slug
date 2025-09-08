<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reset_passwordRequest;
use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdateController extends Controller
{
    public function update(UpdateRequest $updateRequest)
    {
        try {
            $user = User::find(Auth::id());
            if (!$user) {
                return redirect()->route('index')->with('error', 'User not found.');
            }

            $validated = $updateRequest->validated();

            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withInput($updateRequest->only('email', 'name'))->with('error', 'The provided current password does not match our records.');
            }
            unset($validated['current_password'], $validated['password_confirmation']);
            $user->update($validated);

            return redirect()->route('index')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('index')->with('error', 'Failed to update profile. ' . $e->getMessage());
        }
    }

    public function reset_password(Reset_passwordRequest $request)
    {
        try {
            $user = User::find(Auth::id());
            $validated = $request->validated();
            if (!$user) {
                return redirect()->route('index')->with('error', 'User not found.');
            }

            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withInput($request->only('email', 'name'))->with('error', 'The provided current password does not match our records.');
            }

            if (isset($validated['password']) && !empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
            unset($validated['current_password'], $validated['password_confirmation']);
            $user->update($validated);
            return redirect()->route('index')->with('success', 'Password reset successfully.');

        } catch (\Exception $e) {
            return redirect()->route('index')->with('error', 'Failed to reset password. ' . $e->getMessage());
        }
    }
}
