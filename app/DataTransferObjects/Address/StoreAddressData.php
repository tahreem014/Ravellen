<?php

namespace App\DataTransferObjects\Address;

use App\Http\Requests\Address\StoreAddressRequest;

final readonly class StoreAddressData
{
    public function __construct(
        public string $name,
        public string $address,
        public string $country,
        public string $city,
        public ?string $phone,
        public bool $isDefault,
    ) {
    }

    public static function fromRequest(StoreAddressRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            address: $request->validated('address'),
            country: $request->validated('country'),
            city: $request->validated('city'),
            phone: $request->validated('phone'),
            isDefault: $request->boolean('is_default'),
        );
    }
}
