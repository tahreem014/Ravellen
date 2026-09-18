<?php

namespace App\DataTransferObjects\Address;

use App\Models\Address;

final readonly class AddressData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $address,
        public ?string $country,
        public ?string $city,
        public ?string $phone,
        public bool $isDefault,
    ) {
    }

    public static function fromModel(Address $address): self
    {
        return new self(
            id: $address->id,
            name: $address->name,
            address: $address->address,
            country: $address->country,
            city: $address->city,
            phone: $address->phone,
            isDefault: $address->is_default,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'address'    => $this->address,
            'country'    => $this->country,
            'city'       => $this->city,
            'phone'      => $this->phone,
            'is_default' => $this->isDefault,
        ];
    }
}
