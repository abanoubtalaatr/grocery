<?php 

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;

class StoreAddressAction
{
    public function handle(User $user, array $data): Address
    {
        $data['is_default'] = (bool) ($data['is_default'] ?? false)
            || ! $user->addresses()->exists();

        $phone = trim((string) ($data['phone'] ?? ''));
        $countryCode = trim((string) ($data['country_code'] ?? ''));
        if ($countryCode !== '' && str_starts_with($phone, $countryCode)) {
            $data['phone'] = substr($phone, strlen($countryCode));
        }

        return $user->addresses()->create($data);
    }
}