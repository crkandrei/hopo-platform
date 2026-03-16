<?php

namespace App\Enums;

enum WebhookStatus: string
{
    case Processed = 'processed';
    case Failed    = 'failed';
    case Skipped   = 'skipped';
}
