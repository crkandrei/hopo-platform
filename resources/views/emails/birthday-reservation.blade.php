<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervare nouă zi de naștere</title>
    <style>
        @media only screen and (max-width: 600px) {
            .cta-table { width: 100% !important; }
            .cta-cell { display: block !important; width: 100% !important; padding-bottom: 12px !important; }
            .cta-btn { width: 100% !important; display: block !important; box-sizing: border-box !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial,Helvetica,sans-serif; color:#1f2937;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f4f6;">
    <tr>
        <td align="center" style="padding:32px 16px;">

            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.07);">

                {{-- Header --}}
                <tr>
                    <td style="background-color:#6366f1; padding:32px 40px; text-align:center;">
                        <p style="margin:0 0 8px 0; font-size:36px; line-height:1;">🎂</p>
                        <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:700; letter-spacing:-0.3px;">Rezervare nouă zi de naștere</h1>
                        <p style="margin:8px 0 0 0; color:#c7d2fe; font-size:14px;">{{ $reservation->location->name ?? 'Hopo Platform' }}</p>
                    </td>
                </tr>

                {{-- Hero --}}
                <tr>
                    <td style="background-color:#eef2ff; padding:24px 40px; text-align:center; border-bottom:1px solid #e0e7ff;">
                        <p style="margin:0 0 4px 0; font-size:13px; color:#6366f1; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Copil</p>
                        <h2 style="margin:0 0 12px 0; font-size:28px; color:#1e1b4b; font-weight:800;">{{ $reservation->child_name }}</h2>
                        <p style="margin:0; font-size:18px; color:#4338ca; font-weight:600;">
                            {{ $reservation->reservation_date->format('d.m.Y') }}
                            &nbsp;·&nbsp;
                            {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }}
                        </p>
                    </td>
                </tr>

                {{-- Details table --}}
                <tr>
                    <td style="padding:32px 40px;">
                        <h3 style="margin:0 0 16px 0; font-size:14px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.5px; border-bottom:2px solid #e5e7eb; padding-bottom:8px;">Detalii rezervare</h3>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            @php $rows = [
                                ['Copil', $reservation->child_name],
                                ['Vârstă', $reservation->child_age ? $reservation->child_age . ' ani' : '—'],
                                ['Data', $reservation->reservation_date->format('d.m.Y')],
                                ['Ora', \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i')],
                                ['Sală', $reservation->birthdayHall->name ?? '—'],
                                ['Pachet', $reservation->birthdayPackage->name ?? '—'],
                                ['Nr. copii', $reservation->number_of_children],
                                ['Tutore', $reservation->guardian_name],
                                ['Telefon', $reservation->guardian_phone],
                                ['Email', $reservation->guardian_email ?: '—'],
                                ['Observații', $reservation->notes ?: '—'],
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

                {{-- CTA Buttons --}}
                <tr>
                    <td style="padding:8px 40px 40px 40px; text-align:center;">
                        <p style="margin:0 0 20px 0; font-size:15px; color:#374151; font-weight:500;">Acțiune necesară — confirmați sau respingeți rezervarea:</p>

                        <table class="cta-table" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                            <tr>
                                <td class="cta-cell" style="padding-right:12px;">
                                    <a href="{{ $confirmUrl }}" class="cta-btn"
                                       style="display:inline-block; background-color:#10b981; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:14px 28px; border-radius:8px; letter-spacing:0.2px;">
                                        ✓ &nbsp;Confirmă rezervarea
                                    </a>
                                </td>
                                <td class="cta-cell">
                                    <a href="{{ $rejectUrl }}" class="cta-btn"
                                       style="display:inline-block; background-color:#ef4444; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:14px 28px; border-radius:8px; letter-spacing:0.2px;">
                                        ✗ &nbsp;Respinge rezervarea
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:20px 0 0 0; font-size:12px; color:#9ca3af;">
                            Linkurile sunt unice și valabile o singură dată.
                        </p>
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
