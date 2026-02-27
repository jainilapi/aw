<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\AwCart;
use App\Models\AwWishlist;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->method() == 'POST') {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if ($user && !$user->email_verified_at) {
                return back()->withErrors([
                    'email' => 'Please verify your email address before logging in.',
                ])->onlyInput('email');
            }

            if (auth()->guard('customer')->attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                $user = auth()->guard('customer')->user();
                self::saveAccount($user->id);

                $guestId = $request->cookie('guest_id');
                if ($guestId) {
                    try {
                        AwCart::mergeGuestCartToUser($guestId, $user->id);
                        AwWishlist::mergeGuestWishlistToUser($guestId, $user->id);
                    } catch (\Exception $e) {
                        \Log::error('Failed to sync cart/wishlist on login: ' . $e->getMessage());
                    }
                }

                $cookie = cookie('guest_cart', '', -1);

                return redirect()->intended(route('home'))->cookie($cookie);
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        if (auth()->guard('customer')->check()) {
            return redirect()->route('home');
        }
        return view('frontend.login');
    }

    public static function saveAccount($id)
    {
        $savedAccounts = session()->get('saved_accounts', []);

        if (!in_array($id, $savedAccounts)) {
            $savedAccounts[] = $id;

            session()->put('saved_accounts', $savedAccounts);
        }
    }

    public function register(Request $request)
    {
        if ($request->method() == 'POST') {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $token = Str::random(64);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'verification_token' => $token,
                'verification_token_expires_at' => now()->addMinutes(30)
            ]);

            $customerRole = \Spatie\Permission\Models\Role::where('slug', 'customer')->first();
            if ($customerRole) {
                $user->roles()->attach($customerRole->id);
            }

            \App\Jobs\SendVerificationEmail::dispatch($user, $token);

            return redirect()->route('login')->with('success', 'Registration successful! Please check your email to verify your account.');
        }

        return view('frontend.register');
    }

    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid verification token.');
        }

        if ($user->verification_token_expires_at < now()) {
            return redirect()->route('login')->with('error', 'Verification token has expired.');
        }

        self::saveAccount($user->id);

        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
            'verification_token_expires_at' => null,
            'status' => 1,
        ]);

        return redirect()->route('login')->with('success', 'Email verified successfully! You can now login.');
    }

    public function logout(Request $request)
    {
        if (!auth()?->guard('customer')?->check()) {
            return redirect()->route('home');
        }

        $savedAccounts = $request->session()->get('saved_accounts');

        $id = auth()?->guard('customer')?->user()?->id;
        auth()->guard('customer')->logout();

        $request->session()->invalidate();

        if ($savedAccounts) {
            $request->session()->put('saved_accounts', $savedAccounts);

            $savedAccounts = session()->get('saved_accounts', []);

            if (($key = array_search($id, $savedAccounts)) !== false) {
                unset($savedAccounts[$key]);
                session()->put('saved_accounts', array_values($savedAccounts));
            }
        }

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function switchAccount(Request $request)
    {
        $savedAccounts = session()->get('saved_accounts', []);

        if (empty($savedAccounts)) {
            return redirect()->route('login');
        }

        $accounts = User::select('id', 'name', 'profile')->whereIn('id', $savedAccounts)->get();

        return view('frontend.switch-account', compact('accounts'));
    }

    public function removeAccount($id)
    {
        if (auth()?->guard('customer')?->check() && auth()?->guard('customer')?->user()?->id == $id) {
            $savedAccounts = request()->session()->get('saved_accounts');

            auth()->guard('customer')->logout();

            request()->session()->invalidate();

            if ($savedAccounts) {
                request()->session()->put('saved_accounts', $savedAccounts);
            }

            request()->session()->regenerateToken();

            return redirect()->route('switch-account')->with('success', 'Account removed successfully.');
        } else {
            $savedAccounts = session()->get('saved_accounts', []);

            if (($key = array_search($id, $savedAccounts)) !== false) {
                unset($savedAccounts[$key]);
                session()->put('saved_accounts', array_values($savedAccounts));
            }

            return redirect()->route('switch-account')->with('success', 'Account removed successfully.');
        }
    }

    public function addNewAccount()
    {
        if (auth()->guard('customer')->check()) {
            return redirect()->route('switch-account')->with('error', 'you need to logout first from current logged in account');
        }

        return redirect()->route('login');
    }
}
