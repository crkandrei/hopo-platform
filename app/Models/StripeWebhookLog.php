<?php

namespace App\Models;

use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Model;

class StripeWebhookLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'status',
        'location_id',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'status'       => WebhookStatus::class,
        'processed_at' => 'datetime',
        'created_at'   => 'datetime',
    ];
}
