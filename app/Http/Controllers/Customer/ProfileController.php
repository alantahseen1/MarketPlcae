<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit(): View
    {
        $customer = auth('customer')->user();

        return view('customer.profile.edit', compact('customer'));
    }

    /**
     * Update customer profile.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $customer->full_name = (string) $request->input('full_name');
        $customer->phone = (string) $request->input('phone');
        $customer->address = (string) $request->input('address');

        if ($customer->type === 'shop') {
            $customer->shop_name = (string) $request->input('shop_name');

            if ($request->hasFile('logo')) {
                if ($customer->logo && Storage::disk('public')->exists($customer->logo)) {
                    Storage::disk('public')->delete($customer->logo);
                }

                $customer->logo = $request->file('logo')->store('logos', 'public');
            }
        }

        $customer->save();

        return redirect()
            ->route('customer.profile.edit')
            ->with('success', __('profile.profile_updated'));
    }

    /**
     * Show the password change form.
     */
    public function editPassword(): View
    {
        return view('customer.profile.password');
    }

    /**
     * Update customer password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var Customer $customer */
        $customer = auth('customer')->user();

        if (! Hash::check($request->input('current_password'), $customer->password_hash)) {
            throw ValidationException::withMessages([
                'current_password' => __('profile.current_password_incorrect'),
            ]);
        }

        $customer->password_hash = Hash::make($request->input('new_password'));
        $customer->save();

        // Keep the user logged in
        auth('customer')->login($customer);

        return redirect()
            ->route('customer.password.edit')
            ->with('success', __('profile.password_updated'));
    }
}
