<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActionLogger
{
    /**
     * Log an important action with context.
     *
     * @param string $action The action being performed (e.g., 'scan.created', 'session.started')
     * @param string $entityType The type of entity (e.g., 'ScanEvent', 'PlaySession', 'Child')
     * @param int|null $entityId The ID of the entity
     * @param array $context Additional context data
     * @param string $level Log level (info, warning, error)
     * @return void
     */
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $context = [],
        string $level = 'info'
    ): void {
        $user = Auth::user();
        $location = $user?->location;
        $company = $user?->company;

        $logData = [
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'location_id' => $location?->id,
            'location_name' => $location?->name,
            'company_id' => $company?->id,
            'company_name' => $company?->name,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'timestamp' => now()->toIso8601String(),
            'context' => $context,
        ];

        Log::channel('actions')->{$level}($action, $logData);
    }

    /**
     * Log a scan-related action.
     *
     * @param string $action Action type (created, validated, expired)
     * @param string $code The scan code
     * @param array $context Additional context
     * @return void
     */
    public static function logScan(string $action, string $code, array $context = []): void
    {
        self::log(
            "scan.{$action}",
            'ScanEvent',
            null,
            array_merge(['code' => $code], $context)
        );
    }

    /**
     * Log a session-related action.
     *
     * @param string $action Action type (started, stopped, paused, resumed)
     * @param int $sessionId The session ID
     * @param array $context Additional context
     * @return void
     */
    public static function logSession(string $action, int $sessionId, array $context = []): void
    {
        self::log(
            "session.{$action}",
            'PlaySession',
            $sessionId,
            $context
        );
    }

    /**
     * Log a CRUD operation.
     *
     * @param string $operation The operation (created, updated, deleted)
     * @param string $entityType The entity type
     * @param int $entityId The entity ID
     * @param array $context Additional context (e.g., changes)
     * @return void
     */
    public static function logCrud(
        string $operation,
        string $entityType,
        int $entityId,
        array $context = []
    ): void {
        self::log(
            "{$entityType}.{$operation}",
            $entityType,
            $entityId,
            $context
        );
    }

    /**
     * Log an error with full context.
     *
     * @param \Throwable $exception The exception
     * @param array $context Additional context
     * @return void
     */
    public static function logError(\Throwable $exception, array $context = []): void
    {
        $user = Auth::user();
        $location = $user?->location;
        $company = $user?->company;

        $logData = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'location_id' => $location?->id,
            'location_name' => $location?->name,
            'company_id' => $company?->id,
            'company_name' => $company?->name,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'timestamp' => now()->toIso8601String(),
            'context' => $context,
        ];

        Log::channel('errors')->error($exception->getMessage(), $logData);
    }

    /**
     * Log an audit trail entry (also saves to AuditLog model).
     *
     * @param string $action The action performed
     * @param string $entityType The entity type
     * @param int|null $entityId The entity ID
     * @param array|null $dataBefore Data before the change
     * @param array|null $dataAfter Data after the change
     * @param array $context Additional context
     * @return void
     */
    public static function logAudit(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $dataBefore = null,
        ?array $dataAfter = null,
        array $context = []
    ): void {
        $user = Auth::user();
        $location = $user?->location;
        $company = $user?->company;

        $logData = [
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'location_id' => $location?->id,
            'location_name' => $location?->name,
            'company_id' => $company?->id,
            'company_name' => $company?->name,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'timestamp' => now()->toIso8601String(),
            'context' => $context,
        ];

        // Log to audit channel
        Log::channel('audit')->info($action, $logData);

        // Also save to AuditLog model if location and user are available
        if ($location && $user) {
            try {
                \App\Models\AuditLog::create([
                    'location_id' => $location->id,
                    'user_id' => $user->id,
                    'action' => $action,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'data_before' => $dataBefore,
                    'data_after' => $dataAfter,
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);
            } catch (\Exception $e) {
                // If audit log creation fails, log the error but don't break the flow
                Log::channel('errors')->warning('Failed to create audit log entry', [
                    'error' => $e->getMessage(),
                    'audit_data' => $logData,
                ]);
            }
        }
    }
}





