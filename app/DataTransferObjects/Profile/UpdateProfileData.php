<?php

namespace App\DataTransferObjects\Profile;

use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\UploadedFile;

final readonly class UpdateProfileData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phoneNumber,
        public ?UploadedFile $avatar,
    ) {
    }

    public static function fromRequest(UpdateProfileRequest $request): self
    {
        $user = $request->user();

        return new self(
            name: $request->input('name', $user->name),
            email: $request->input('email', $user->email),
            phoneNumber: $request->input('phone_number', $user->phone_number),
            avatar: $request->file('avatar'),
        );
    }
}
