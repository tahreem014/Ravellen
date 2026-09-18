<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Profile\UpdatePasswordData;
use App\DataTransferObjects\Profile\UpdateProfileData;
use App\Http\Requests\Profile\DeleteProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Order;
use App\Services\AddressService;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly AddressService $addressService,
    ) {
    }

    public function show(Request $request): View
    {
        $profile = $this->profileService->getProfile($request->user());
        $addresses = $this->addressService->listAddresses($request->user());
        $orders = $request->user()->orders()
            ->with(['items.product.images', 'items.productVariant', 'items.refundRequest', 'items.review'])
            ->orderByDesc('order_date')
            ->get();
        $totalSpent = $orders->where('order_status', '!=', Order::STATUS_CANCELLED)->sum('total_amount');
        $wishlistCount = $request->user()->wishLists()->count();

        return view('pages.profile', [
            'profile'       => $profile,
            'addresses'     => $addresses,
            'orders'        => $orders,
            'totalSpent'    => $totalSpent,
            'wishlistCount' => $wishlistCount,
        ]);
    }

    public function get(Request $request): JsonResponse
    {
        $profile = $this->profileService->getProfile($request->user());

        return response()->json($profile->toArray());
    }

    public function edit(UpdateProfileRequest $request): RedirectResponse|JsonResponse
    {
        $this->profileService->updateProfile(
            $request->user(),
            UpdateProfileData::fromRequest($request)
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Profile updated successfully.']);
        }

        return redirect()->route('profile')->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse|JsonResponse
    {
        $this->profileService->updatePassword(
            $request->user(),
            UpdatePasswordData::fromRequest($request)
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password updated successfully.']);
        }

        return redirect()->route('profile', ['tab' => 'settings'])->with('status', 'Password updated successfully.');
    }

    public function delete(DeleteProfileRequest $request): RedirectResponse|JsonResponse
    {
        $deleted = $this->profileService->deleteProfile(
            $request->user(),
            $request->validated('password')
        );

        if (! $deleted) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The password is incorrect.',
                    'errors'  => ['password' => ['The password is incorrect.']],
                ], 422);
            }

            return redirect()->route('profile', ['tab' => 'settings'])
                ->withErrors(['password' => 'The password is incorrect.'], 'deleteAccount');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('login')]);
        }

        return redirect()->route('login')->with('status', 'Your account has been deleted.');
    }
}
