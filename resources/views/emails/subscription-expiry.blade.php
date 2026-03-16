<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonament pe cale să expire</title>
</head>
<body style="margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; color:#333333; background-color:#ffffff;">

<div style="max-width:580px; margin:0 auto; padding:24px 16px;">

    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Bună ziua,
    </p>

    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Vă informăm că abonamentul pentru locația <strong>{{ $subscription->location->name }}</strong>
        ({{ $subscription->location->company->name }}) expiră în <strong>{{ $daysRemaining }} {{ $daysRemaining === 1 ? 'zi' : 'zile' }}</strong>,
        pe data de <strong>{{ $subscription->expires_at->format('d.m.Y') }}</strong>.
    </p>

    @if(isset($recipientType) && $recipientType === 'company_admin')
    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Puteți reînnoi abonamentul direct din aplicație:
    </p>
    <p style="margin:0 0 20px 0;">
        <a href="{{ route('checkout.plans') }}"
           style="display:inline-block; padding:12px 24px; background:#4f46e5; color:#ffffff; text-decoration:none; border-radius:8px; font-size:14px; font-weight:bold;">
            Reînnoiește abonamentul acum
        </a>
    </p>
    <p style="margin:0 0 20px 0; font-size:14px; color:#6b7280; line-height:1.6;">
        Sau contactați-ne la <strong>contact@hopo.ro</strong> pentru reînnoire manuală.
    </p>
    @else
    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Pentru reînnoirea abonamentului, vă rugăm să ne contactați la <strong>contact@hopo.ro</strong>.
    </p>
    @endif

    <p style="margin:0 0 4px 0; font-size:15px; color:#333333; line-height:1.6;">
        Cu respect,<br>
        Echipa Hopo
    </p>

</div>

</body>
</html>
