<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plată eșuată</title>
</head>
<body style="margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; color:#333333; background-color:#ffffff;">

<div style="max-width:580px; margin:0 auto; padding:24px 16px;">

    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Bună ziua,
    </p>

    <p style="margin:0 0 20px 0; font-size:15px; color:#dc2626; font-weight:bold; line-height:1.6;">
        Atenție: o plată prin Stripe a eșuat.
    </p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:14px;">
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold; width:40%;">Locație</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $location->name }}</td>
        </tr>
        @if($plan)
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Plan</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $plan->name }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; font-weight:bold;">Stripe Event ID</td>
            <td style="padding:8px 12px; border:1px solid #e5e7eb; font-family:monospace; font-size:12px;">{{ $stripeEventId }}</td>
        </tr>
    </table>

    <p style="margin:0 0 20px 0; font-size:14px; color:#6b7280; line-height:1.6;">
        Verificați detaliile în Stripe Dashboard. Abonamentul nu a fost activat.
    </p>

    <p style="margin:0 0 4px 0; font-size:15px; color:#333333; line-height:1.6;">
        Cu respect,<br>
        Sistemul Hopo Platform
    </p>

</div>

</body>
</html>
