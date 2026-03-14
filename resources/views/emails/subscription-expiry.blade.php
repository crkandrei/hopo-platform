<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonament pe cale să expire</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial,Helvetica,sans-serif; color:#1f2937;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f4f6;">
    <tr>
        <td align="center" style="padding:32px 16px;">

            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.07);">

                {{-- Header --}}
                <tr>
                    <td style="background-color:#f59e0b; padding:32px 40px; text-align:center;">
                        <p style="margin:0 0 8px 0; font-size:36px; line-height:1;">⚠️</p>
                        <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:700; letter-spacing:-0.3px;">Abonament pe cale să expire</h1>
                        <p style="margin:8px 0 0 0; color:#fef3c7; font-size:14px;">{{ $subscription->location->name }}</p>
                    </td>
                </tr>

                {{-- Details table --}}
                <tr>
                    <td style="padding:32px 40px;">
                        <h3 style="margin:0 0 16px 0; font-size:14px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.5px; border-bottom:2px solid #e5e7eb; padding-bottom:8px;">Detalii abonament</h3>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            @php $rows = [
                                ['Locație',      $subscription->location->name],
                                ['Companie',     $subscription->location->company->name],
                                ['Expiră la',    $subscription->expires_at->format('d M Y')],
                                ['Zile rămase',  $daysRemaining],
                            ]; @endphp

                            @foreach($rows as $i => [$label, $value])
                            <tr style="{{ $i % 2 === 0 ? 'background-color:#f9fafb;' : '' }}">
                                <td style="padding:10px 12px; font-size:13px; font-weight:600; color:#6b7280; width:130px; white-space:nowrap;">{{ $label }}</td>
                                <td style="padding:10px 12px; font-size:14px; color:#111827;">{{ $value }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                {{-- CTA --}}
                <tr>
                    <td style="padding:8px 40px 40px 40px; text-align:center;">
                        <p style="margin:0 0 8px 0; font-size:15px; color:#374151; font-weight:500;">
                            Contactați administratorul HOPO pentru reînnoire.
                        </p>
                        <p style="margin:0; font-size:14px; color:#f59e0b; font-weight:600;">contact@hopo.ro</p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background-color:#f9fafb; border-top:1px solid #e5e7eb; padding:20px 40px; text-align:center;">
                        <p style="margin:0; font-size:12px; color:#9ca3af;">
                            Hopo Platform &nbsp;·&nbsp; Email generat automat &nbsp;·&nbsp; Nu răspundeți la acest email
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
