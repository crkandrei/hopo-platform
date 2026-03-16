<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonament activat</title>
</head>
<body style="margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; color:#333333; background-color:#ffffff;">

<div style="max-width:580px; margin:0 auto; padding:24px 16px;">

    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Bună ziua,
    </p>

    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Abonamentul pentru locația <strong>{{ $subscription->location->name }}</strong>
        ({{ $subscription->location->company->name ?? '—' }}) a fost activat cu succes.
    </p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:14px;">
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold; width:40%;">Sursă</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $source === 'stripe' ? 'Plată online (Stripe)' : 'Manual (admin)' }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Activ din</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $subscription->starts_at->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Expiră pe</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $subscription->expires_at->format('d.m.Y') }}</td>
        </tr>
        @if($subscription->price_paid)
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Sumă plătită</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ number_format($subscription->price_paid, 2, ',', '.') }} RON</td>
        </tr>
        @endif
        @if($subscription->stripe_session_id)
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Stripe Session</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb; font-family:monospace; font-size:12px;">{{ $subscription->stripe_session_id }}</td>
        </tr>
        @endif
    </table>

    <p style="margin:0 0 4px 0; font-size:15px; color:#333333; line-height:1.6;">
        Cu respect,<br>
        Sistemul Hopo Platform
    </p>

</div>

</body>
</html>
