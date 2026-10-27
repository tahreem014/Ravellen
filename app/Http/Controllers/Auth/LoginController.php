<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\CheckoutResume;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Where to redirect after logout — the login page, not the guarded default.
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('login');
    }

    /**
     * Validate login credentials and block suspended accounts.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);
    }

    /**
     * After credentials pass, check if account is blocked, then land on the
     * admin dashboard for admins or the home page for everyone else — not
     * wherever the "guest" middleware intercepted the user from (e.g. profile).
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->is_blocked) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // A guest who pressed "Place Order" carries on to payment instead of landing on the home page
        if (CheckoutResume::pending()) {
            return redirect()->route('checkout');
        }

        return redirect($this->redirectPath());
    }

    /**
     * Return custom failed login message.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    /**
     * Redirect to Google OAuth.
     */
    // public function redirectToGoogle()
    // {
    //     return Socialite::driver('google')->redirect();
    // }

    // /**
    //  * Handle Google OAuth callback.
    //  */
    // public function handleGoogleCallback()
    // {
    //     $googleUser = Socialite::driver('google')->stateless()->user();

    //     $user = User::firstOrCreate(
    //         ['email' => $googleUser->getEmail()],
    //         [
    //             'name'              => $googleUser->getName(),
    //             'password'          => bcrypt(Str::random(24)),
    //             'avatar'            => $googleUser->getAvatar(),
    //             'email_verified_at' => now(),
    //         ]
    //     );

    //     if ($user->is_blocked) {
    //         return redirect()->route('login')
    //             ->withErrors(['email' => 'Your account has been suspended.']);
    //     }

    //     Auth::login($user, true);

    //     return redirect($this->redirectTo);
    // }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if (! $user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(Str::random(16)), // random password
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            // Check if blocked
            if ($user->is_blocked) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been suspended.',
                ]);
            }

            // Login user
            Auth::login($user, true);

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if (CheckoutResume::pending()) {
                return redirect()->route('checkout');
            }

            return redirect('/');

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google login failed. Try again.',
            ]);
        }
    }
}
