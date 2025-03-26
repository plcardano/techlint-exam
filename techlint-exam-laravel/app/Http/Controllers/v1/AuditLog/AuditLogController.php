<?php

namespace App\Http\Controllers\v1\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AuditLog\AuditLogResource;
use App\Services\v1\AuditLog\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Display a listing of all audit logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $auditLogs = $this->auditLogService->getAllAuditLogs($request);

        return response()->json(
            AuditLogResource::collection($auditLogs)
                ->response()
                ->getData(true)
        );
    }

    /**
     * Display a listing of audit logs for a specific IP address.
     *
     * @param Request $request
     * @param int $ipAddressId
     * @return JsonResponse
     */
    public function forIpAddress(Request $request, int $ipAddressId): JsonResponse
    {
        $auditLogs = $this->auditLogService->getAuditLogsForIpAddress($ipAddressId, $request);

        return response()->json(
            AuditLogResource::collection($auditLogs)
                ->response()
                ->getData(true)
        );
    }

    /**
     * Display a listing of audit logs for a specific user.
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function forUser(Request $request, int $userId): JsonResponse
    {
        $auditLogs = $this->auditLogService->getAuditLogsForUser($userId, $request);

        return response()->json(
            AuditLogResource::collection($auditLogs)
                ->response()
                ->getData(true)
        );
    }

    /**
     * Display a listing of audit logs for a specific session.
     *
     * @param Request $request
     * @param string $sessionId
     * @return JsonResponse
     */
    public function forSession(Request $request, string $sessionId): JsonResponse
    {
        $auditLogs = $this->auditLogService->getAuditLogsForSession($sessionId, $request);

        return response()->json(
            AuditLogResource::collection($auditLogs)
                ->response()
                ->getData(true)
        );
    }

    /**
     * Display a listing of audit logs for the current user's session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forCurrentSession(Request $request): JsonResponse
    {
        $auditLogs = $this->auditLogService->getAuditLogsForCurrentSession($request);

        return response()->json(
            AuditLogResource::collection($auditLogs)
                ->response()
                ->getData(true)
        );
    }
}
