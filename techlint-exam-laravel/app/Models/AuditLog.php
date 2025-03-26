<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'session_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            throw new \Exception('Audit logs cannot be deleted');
        });
    }

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all audit logs for a specific IP address.
     */
    public static function forIpAddress(int $ipAddressId): Builder
    {
        return self::query()->where('entity_type', 'ip_address')
            ->where('entity_id', $ipAddressId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all audit logs for a specific user.
     */
    public static function forUser(int $userId): Builder
    {
        return self::query()->where('user_id', $userId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all audit logs for a specific session.
     */
    public static function forSession(string $sessionId): Builder
    {
        return self::query()->where('session_id', $sessionId)
            ->orderBy('created_at', 'desc');
    }
}
