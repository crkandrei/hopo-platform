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
        Plata a fost procesată cu succes și abonamentul pentru locația
        <strong>{{ $subscription->location->name }}</strong> este acum activ.
    </p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:24px; font-size:14px;">
        <tr>
            <td style="padding:10px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold; width:45%;">Locație</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $subscription->location->name }}</td>
        </tr>
        @if($subscription->plan)
        <tr>
            <td style="padding:10px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Plan</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $subscription->plan->name }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding:10px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Activ din</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $subscription->starts_at->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Valabil până pe</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;"><strong>{{ $subscription->expires_at->format('d.m.Y') }}</strong></td>
        </tr>
        @if($subscription->price_paid)
        <tr>
            <td style="padding:10px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Sumă achitată</td>
            <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ number_format($subscription->price_paid, 2, ',', '.') }} RON</td>
        </tr>
        @endif
    </table>

    <p style="margin:0 0 20px 0; font-size:14px; color:#6b7280; line-height:1.6;">
        Dacă aveți întrebări, ne puteți contacta la <strong>contact@hopo.ro</strong>.
    </p>

    <p style="margin:0 0 4px 0; font-size:15px; color:#333333; line-height:1.6;">
        Cu respect,<br>
        Echipa Hopo
    </p>

</div>

</body>
</html>
