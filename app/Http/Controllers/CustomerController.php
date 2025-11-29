<?php

namespace App\Http\Controllers;

use App\Models\UserBuild;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index() {
        $authId = Auth::user()->id;
        $userBuilds = UserBuild::with([
            'case',
            'cpu',
            'motherboard',
            'gpu',
            'ram',
            'storage',
            'psu',
            'cooler',
            'user'
        ])->where('user_id', $authId)->paginate(5);

        return view('customer.dashboard', compact('userBuilds'));
    }

    public function update(Request $request) {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
        ]);

        $emailChanged = $request->email !== $user->email;

        $user->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ]);

        return redirect()->route('customer.dashboard')->with([
            'message' => $emailChanged
                ? 'Profile Updated.'
                : 'Profile updated successfully.',
            'type' => 'success',
        ]);
    }
}
