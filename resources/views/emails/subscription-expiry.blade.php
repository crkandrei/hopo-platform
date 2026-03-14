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

    <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
        Pentru reînnoirea abonamentului, vă rugăm să ne contactați la <strong>contact@hopo.ro</strong>.
    </p>

    <p style="margin:0 0 4px 0; font-size:15px; color:#333333; line-height:1.6;">
        Cu respect,<br>
        Echipa Hopo
    </p>

</div>

</body>
</html>
