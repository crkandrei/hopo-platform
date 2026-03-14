<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport zilnic — {{ $reportData->date->format('d.m.Y') }}</title>
</head>
<body style="margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; color:#333333; background-color:#f5f5f5;">

<div style="max-width:620px; margin:0 auto; padding:24px 16px;">

    <!-- Header -->
    <div style="background-color:#4f46e5; border-radius:8px 8px 0 0; padding:24px 28px;">
        <h1 style="margin:0; font-size:22px; color:#ffffff; font-weight:700;">
            Raport zilnic — {{ $reportData->date->format('d.m.Y') }}
        </h1>
        <p style="margin:6px 0 0 0; font-size:14px; color:#c7d2fe;">
            {{ $reportData->company->name }}
        </p>
    </div>

    <!-- Body -->
    <div style="background-color:#ffffff; border-radius:0 0 8px 8px; padding:28px; border:1px solid #e5e7eb; border-top:none;">

        <p style="margin:0 0 20px 0; font-size:15px; color:#333333; line-height:1.6;">
            Bună ziua,
        </p>

        <p style="margin:0 0 24px 0; font-size:15px; color:#333333; line-height:1.6;">
            Mai jos găsiți raportul activității din <strong>{{ $reportData->date->format('d.m.Y') }}</strong>
            pentru toate locațiile companiei <strong>{{ $reportData->company->name }}</strong>.
        </p>

        <!-- Locations table -->
        @foreach($reportData->locationReports as $locationReport)
        <div style="margin-bottom:24px;">
            <h2 style="margin:0 0 10px 0; font-size:16px; color:#1f2937; font-weight:600; border-bottom:2px solid #e5e7eb; padding-bottom:6px;">
                {{ $locationReport->location->name }}
            </h2>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background-color:#f9fafb;">
                        <th style="padding:10px 12px; text-align:left; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Sesiuni</th>
                        <th style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Ore facturate</th>
                        <th style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Cash</th>
                        <th style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Card</th>
                        <th style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Voucher</th>
                        <th style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:700; white-space:nowrap;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:10px 12px; border:1px solid #e5e7eb; color:#1f2937;">{{ $locationReport->totalSessions }}</td>
                        <td style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">
                            @php
                                $h = floor($locationReport->totalBilledHours);
                                $m = round(($locationReport->totalBilledHours - $h) * 60);
                                if ($m >= 60) { $h++; $m = 0; }
                            @endphp
                            {{ $h }}h{{ $m > 0 ? ' ' . $m . 'm' : '' }}
                        </td>
                        <td style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($locationReport->cashTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($locationReport->cardTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($locationReport->voucherTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:10px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937; font-weight:700;">{{ number_format($locationReport->totalMoney, 2, ',', '.') }} RON</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach

        <!-- Grand total -->
        @if($reportData->locationReports->count() > 1)
        <div style="margin-top:8px; padding:14px 16px; background-color:#eff6ff; border-radius:6px; border:1px solid #bfdbfe;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:15px; font-weight:700; color:#1e40af;">Total general</span>
                <span style="font-size:18px; font-weight:700; color:#1e40af;">{{ number_format($reportData->grandTotalMoney, 2, ',', '.') }} RON</span>
            </div>
        </div>
        @endif

        <p style="margin:28px 0 8px 0; font-size:15px; color:#333333; line-height:1.6;">
            Cu respect,<br>
            Echipa Hopo
        </p>

    </div>

    <!-- Footer -->
    <p style="margin:16px 0 0 0; font-size:12px; color:#9ca3af; text-align:center;">
        Acest email a fost generat automat de platforma Hopo. Pentru detalii, accesați dashboard-ul.
    </p>

</div>

</body>
</html>
