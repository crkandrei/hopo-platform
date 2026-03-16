<?php

namespace App\Enums;

enum PaymentSource: string
{
    case Manual = 'manual';
    case Stripe = 'stripe';
}
