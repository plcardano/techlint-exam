<?php

namespace App\Services\v1\IpAddress;

use App\Models\IpAddress;
use App\Services\v1\AuditLog\AuditLogService;
use Illuminate\Support\Facades\Auth;

class IpAddressService
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Store a new IP address
     *
     * @param array $data
     * @return IpAddress
     */
    public function storeIpAddress(array $data): IpAddress
    {
        $data['user_id'] = Auth::id();

        $ipAddress = IpAddress::create($data);
        $this->auditLogService->logModel('create', $ipAddress, null, $ipAddress->toArray());

        return $ipAddress;
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
        $oldValues = $ipAddress->toArray();

        $ipAddress->update($data);

        $this->auditLogService->logModel('update', $ipAddress, $oldValues, $ipAddress->toArray());

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
        $oldValues = $ipAddress->toArray();

        $this->auditLogService->logModel('delete', $ipAddress, $oldValues, null);

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
