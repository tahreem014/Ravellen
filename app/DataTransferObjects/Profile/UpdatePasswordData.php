<?php

namespace App\DataTransferObjects\Profile;

use App\Http\Requests\Profile\UpdatePasswordRequest;

final readonly class UpdatePasswordData
{
    public function __construct(
        public string $password,
    ) {
    }

    public static function fromRequest(UpdatePasswordRequest $request): self
    {
        return new self(
            password: $request->validated('password'),
        );
    }
}
