<?php

namespace App\DataTransferObjects\Address;

use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;

final readonly class UpdateAddressData
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

    public static function fromRequest(UpdateAddressRequest $request, Address $address): self
    {
        return new self(
            name: $request->input('name', $address->name),
            address: $request->input('address', $address->address),
            country: $request->input('country', $address->country),
            city: $request->input('city', $address->city),
            phone: $request->input('phone', $address->phone),
            isDefault: $request->boolean('is_default', $address->is_default),
        );
    }
}
