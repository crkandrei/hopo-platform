<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantConfiguration extends Model
{
    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'array', // Auto-decode JSON
    ];

    /**
     * Get the tenant that owns the configuration.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get configuration value for a tenant (or global if tenant_id is null)
     * 
     * @param int|null $tenantId
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getForTenant(?int $tenantId, string $key, $default = null)
    {
        $config = self::where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        if (!$config) {
            return $default;
        }

        return $config->value;
    }

    /**
     * Set configuration value for a tenant
     * 
     * @param int|null $tenantId
     * @param string $key
     * @param mixed $value
     * @param string|null $type
     * @param string|null $description
     * @return self
     */
    public static function setForTenant(?int $tenantId, string $key, $value, ?string $type = null, ?string $description = null): self
    {
        return self::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'key' => $key,
            ],
            [
                'value' => is_array($value) ? $value : $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }


    /**
     * Get pause warning threshold in minutes for a tenant
     * Returns the threshold for showing pause warnings (default: 15 minutes)
     * 
     * @param int|null $tenantId
     * @return int Threshold in minutes
     */
    public static function getPauseWarningThresholdMinutes(?int $tenantId): int
    {
        $threshold = self::getForTenant($tenantId, 'pause_warning_threshold_minutes', 15);
        
        // Ensure it's an integer
        return (int) $threshold;
    }
}

