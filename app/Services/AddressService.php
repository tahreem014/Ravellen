<?php

namespace App\Services;

use App\DataTransferObjects\Address\AddressData;
use App\DataTransferObjects\Address\StoreAddressData;
use App\DataTransferObjects\Address\UpdateAddressData;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;

class AddressService
{
    public function listAddresses(User $user): Collection
    {
        return $user->addresses()
            ->latest()
            ->get()
            ->map(fn (Address $address) => AddressData::fromModel($address));
    }

    public function getAddress(User $user, Address $address): AddressData
    {
        $this->authorizeOwnership($user, $address);

        return AddressData::fromModel($address);
    }

    public function createAddress(User $user, StoreAddressData $data): AddressData
    {
        if ($data->isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'name'       => $data->name,
            'address'    => $data->address,
            'country'    => $data->country,
            'city'       => $data->city,
            'phone'      => $data->phone,
            'is_default' => $data->isDefault,
        ]);

        return AddressData::fromModel($address);
    }

    public function updateAddress(User $user, Address $address, UpdateAddressData $data): AddressData
    {
        $this->authorizeOwnership($user, $address);

        if ($data->isDefault) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'name'       => $data->name,
            'address'    => $data->address,
            'country'    => $data->country,
            'city'       => $data->city,
            'phone'      => $data->phone,
            'is_default' => $data->isDefault,
        ]);

        return AddressData::fromModel($address->fresh());
    }

    public function deleteAddress(User $user, Address $address): void
    {
        $this->authorizeOwnership($user, $address);

        $address->delete();
    }

    private function authorizeOwnership(User $user, Address $address): void
    {
        abort_unless($address->user_id === $user->id, 403);
    }
}
