<?php

namespace App\Services\v1\AuditLog;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogService
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Log an action
     *
     * @param string $action
     * @param string|null $entityType
     * @param int|null $entityId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return AuditLog
     */
    public function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $userId = Auth::id();
        $sessionId = session()->getId();

        $log = AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'session_id' => $sessionId,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);

        return $log;
    }

    /**
     * Log model action
     *
     * @param string $action
     * @param Model|null $model
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return AuditLog
     */
    public function logModel(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $entityType = $model ? Str::snake(class_basename($model)) : null;
        $entityId = $model ? $model->id : null;

        return $this->log($action, $entityType, $entityId, $oldValues, $newValues);
    }

    /**
     * Log user login
     *
     * @return AuditLog
     */
    public function logLogin(): AuditLog
    {
        return $this->log('login', 'user', Auth::id());
    }

    /**
     * Log user logout
     *
     * @return AuditLog
     */
    public function logLogout(): AuditLog
    {
        return $this->log('logout', 'user', Auth::id());
    }

    /**
     * Get all audit logs with pagination
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAllAuditLogs(Request $request): LengthAwarePaginator
    {
        return AuditLog::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends($request->query());
    }

    /**
     * Get audit logs for a specific IP address
     *
     * @param int $ipAddressId
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAuditLogsForIpAddress(int $ipAddressId, Request $request): LengthAwarePaginator
    {
        return AuditLog::forIpAddress($ipAddressId)
            ->with('user:id,name,email')
            ->paginate()
            ->appends($request->query());
    }

    /**
     * Get audit logs for a specific user
     *
     * @param int $userId
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAuditLogsForUser(int $userId, Request $request): LengthAwarePaginator
    {
        return AuditLog::forUser($userId)
            ->with('user:id,name,email')
            ->paginate()
            ->appends($request->query());
    }

    /**
     * Get audit logs for a specific session
     *
     * @param string $sessionId
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAuditLogsForSession(string $sessionId, Request $request): LengthAwarePaginator
    {
        return AuditLog::forSession($sessionId)
            ->with('user:id,name,email')
            ->paginate()
            ->appends($request->query());
    }

    /**
     * Get audit logs for a user's current session
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAuditLogsForCurrentSession(Request $request): LengthAwarePaginator
    {
        $sessionId = session()->getId();

        return $this->getAuditLogsForSession($sessionId, $request);
    }
}
