<?php

namespace App\DataTransferObjects\Profile;

use App\Models\User;

final readonly class ProfileData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $phoneNumber,
        public string $avatarUrl,
        public string $memberSince,
        public bool $isBlocked,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            phoneNumber: $user->phone_number,
            avatarUrl: $user->avatar ? asset('images/' . $user->avatar) : asset('images/default-avatar.png'),
            memberSince: $user->created_at->format('F Y'),
            isBlocked: (bool) $user->is_blocked,
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone_number' => $this->phoneNumber,
            'avatar_url'   => $this->avatarUrl,
            'member_since' => $this->memberSince,
            'is_blocked'   => $this->isBlocked,
        ];
    }
}
