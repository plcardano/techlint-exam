<?php

namespace App\Services\v1\IpAddress;

use App\Models\IpAddress;
use Illuminate\Support\Facades\Auth;

class IpAddressService
{
    /**
     * Store a new IP address
     *
     * @param array $data
     * @return IpAddress
     */
    public function storeIpAddress(array $data): IpAddress
    {
        $data['user_id'] = Auth::id();
        return IpAddress::create($data);
    }

    /**
     * Update an IP address
     *
     * @param IpAddress $ipAddress
     * @param array $data
     * @return IpAddress
     */
    public function updateIpAddress(IpAddress $ipAddress, array $data): IpAddress
    {
        $ipAddress->update($data);
        return $ipAddress;
    }

    /**
     * Delete an IP address
     *
     * @param IpAddress $ipAddress
     * @return bool
     */
    public function deleteIpAddress(IpAddress $ipAddress): bool
    {
        return $ipAddress->delete();
    }

    /**
     * Check if a user can edit an IP address
     *
     * @param IpAddress $ipAddress
     * @return bool
     */
    public function canEdit(IpAddress $ipAddress): bool
    {
        $user = Auth::user();

        if ($user->hasPermissionTo('can-edit-any-ip-address')) {
            return true;
        }

        if ($user->hasPermissionTo('can-edit-ip-addresses') && $ipAddress->user_id === $user->id) {
            return true;
        }

        return false;
    }
}
