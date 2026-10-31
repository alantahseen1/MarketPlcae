<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRegisterRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm(): View
    {
        return view('customer.auth.register');
    }

    /**
     * Handle customer registration.
     */
    public function register(CustomerRegisterRequest $request): RedirectResponse
    {
        $logoPath = null;
        if ($request->input('type') === 'shop' && $request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $customer = Customer::create([
            'type' => $request->input('type'),
            'full_name' => $request->input('full_name'),
            'shop_name' => $request->input('type') === 'shop' ? $request->input('shop_name') : null,
            'logo' => $logoPath,
            'phone' => trim((string) $request->input('phone')),
            'email' => $request->input('email'),
            'password_hash' => Hash::make($request->input('password')),
            'address' => $request->input('address'),
            'is_verified' => false,
            'is_banned' => false,
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect('/')->with('success', __('auth.register_success'));
    }

    /**
     * Show the customer login form.
     */
    public function showLoginForm(): View
    {
        return view('customer.auth.login');
    }

    /**
     * Handle customer login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $customer = Customer::where('email', $request->input('email'))->first();

        if (! $customer || ! Hash::check($request->input('password'), $customer->password_hash)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($customer->is_banned) {
            throw ValidationException::withMessages([
                'email' => __('auth.banned'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Handle customer logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', __('auth.logged_out'));
    }
}
