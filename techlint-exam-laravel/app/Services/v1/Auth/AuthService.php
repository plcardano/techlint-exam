<?php

namespace App\Services\v1\Auth;

use App\Http\Resources\v1\IpAddress\UserResource;
use App\Models\User;
use App\Services\v1\AuditLog\AuditLogService;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Attempt to authenticate a user.
     *
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function login(string $email, string $password): ?array
    {
        $credentials = ['email' => $email, 'password' => $password];

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return null;
        }

        $this->auditLogService->logLogin();

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated user.
     *
     * @return User|null
     */
    public function me(): ?User
    {
        return Auth::guard('api')->user();
    }

    /**
     * Log the user out (invalidate the token).
     *
     * @return bool
     */
    public function logout(): bool
    {
        $this->auditLogService->logLogout();

        Auth::guard('api')->logout();
        return true;
    }

    /**
     * Refresh a token.
     *
     * @return array
     */
    public function refreshToken(): array
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     * @return array
     */
    protected function respondWithToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => UserResource::make(Auth::guard('api')->user()),
        ];
    }
}
