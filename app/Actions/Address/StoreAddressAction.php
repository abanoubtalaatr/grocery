<?php 

namespace App\Actions\Address;

class StoreAddressAction {
    
    public function hanlde($data){
        $data = $request->validated();

            $user = $request->user();


            // If this is the first address, make it default
            $isFirstAddress = $user->addresses()->count() === 0;
            $data = array_merge(
                $validator->validated(),
                ['is_default' => $request->boolean('is_default') || $isFirstAddress]
            );
            // Normalize phone: if phone already starts with country code, store only the national part
            $phone = trim($data['phone'] ?? '');
            $code = trim($data['country_code'] ?? '');
            if ($code !== '' && str_starts_with($phone, $code)) {
                $data['phone'] = substr($phone, strlen($code));
            }
            $address = $user->addresses()->create($data);
            return $addresss;
    }
}