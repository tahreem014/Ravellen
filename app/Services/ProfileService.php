<?php

namespace App\Services;

use App\DataTransferObjects\Profile\ProfileData;
use App\DataTransferObjects\Profile\UpdatePasswordData;
use App\DataTransferObjects\Profile\UpdateProfileData;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function getProfile(User $user): ProfileData
    {
        return ProfileData::fromModel($user);
    }

    public function updateProfile(User $user, UpdateProfileData $data): ProfileData
    {
        $user->name = $data->name;
        $user->email = $data->email;
        $user->phone_number = $data->phoneNumber;

        if ($data->avatar) {
            $this->storeAvatar($user, $data->avatar);
        }

        $user->save();

        return ProfileData::fromModel($user->fresh());
    }

    public function updatePassword(User $user, UpdatePasswordData $data): void
    {
        $user->password = Hash::make($data->password);
        $user->save();
    }

    public function deleteProfile(User $user, string $password): bool
    {
        if (! Hash::check($password, $user->password)) {
            return false;
        }

        $user->delete();

        return true;
    }

    private function storeAvatar(User $user, UploadedFile $avatar): void
    {
        $previous = $user->avatar;

        $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
        $avatar->move(public_path('images'), $filename);
        $user->avatar = $filename;

        if ($previous && $previous !== 'default-avatar.png' && file_exists(public_path('images/' . $previous))) {
            @unlink(public_path('images/' . $previous));
        }
    }
}
