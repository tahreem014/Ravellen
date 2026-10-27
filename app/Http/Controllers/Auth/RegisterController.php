<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\CheckoutResume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $avatarName = null;

        if ($request->hasFile('avatar')) {
            $avatar     = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('/images/'), $avatarName);
        }

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'avatar'       => $avatarName,
        ]);

        Auth::login($user);

        // A guest who pressed "Place Order" carries on to payment instead of landing on the home page
        if (CheckoutResume::pending()) {
            return redirect()->route('checkout');
        }

        return redirect($this->redirectTo);
    }
}
