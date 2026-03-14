<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport zilnic — {{ $reportData->date->format('d.m.Y') }}</title>
</head>
<body style="margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; color:#333333; background-color:#f5f5f5;">

<div style="max-width:660px; margin:0 auto; padding:24px 16px;">

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

@if($reportData->hasActivity)
        <p style="margin:0 0 28px 0; font-size:15px; color:#333333; line-height:1.6;">
            Mai jos găsiți raportul activității din <strong>{{ $reportData->date->format('d.m.Y') }}</strong>
            pentru <strong>{{ $reportData->company->name }}</strong>.
        </p>

        @foreach($reportData->locationReports as $locationReport)
        @if($locationReport->grandTotal > 0 || $locationReport->totalSessions > 0)
        <div style="margin-bottom:32px;">
            <h2 style="margin:0 0 14px 0; font-size:17px; color:#1f2937; font-weight:700; border-bottom:2px solid #4f46e5; padding-bottom:8px;">
                {{ $locationReport->location->name }}
            </h2>

            {{-- Sesiuni --}}
            <h3 style="margin:0 0 8px 0; font-size:13px; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Sesiuni</h3>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px; margin-bottom:20px;">
                <thead>
                    <tr style="background-color:#f9fafb;">
                        <th style="padding:9px 12px; text-align:left; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Nr. sesiuni</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Ore facturate</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Cash</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Card</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Voucher</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:700; white-space:nowrap;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:9px 12px; border:1px solid #e5e7eb; color:#1f2937;">{{ $locationReport->totalSessions }}</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">
                            @php
                                $h = floor($locationReport->totalBilledHours);
                                $m = round(($locationReport->totalBilledHours - $h) * 60);
                                if ($m >= 60) { $h++; $m = 0; }
                            @endphp
                            {{ $h }}h{{ $m > 0 ? ' ' . $m . 'm' : '' }}
                        </td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($locationReport->cashTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($locationReport->cardTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($locationReport->voucherTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937; font-weight:700;">{{ number_format($locationReport->totalMoney, 2, ',', '.') }} RON</td>
                    </tr>
                </tbody>
            </table>

            {{-- Produse vandute --}}
            @if($locationReport->productSales->count() > 0)
            <h3 style="margin:0 0 8px 0; font-size:13px; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Produse vândute</h3>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px; margin-bottom:20px;">
                <thead>
                    <tr style="background-color:#f9fafb;">
                        <th style="padding:9px 12px; text-align:left; border:1px solid #e5e7eb; color:#6b7280; font-weight:600;">Produs</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Cant.</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Cash</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Card</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Voucher</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:700; white-space:nowrap;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locationReport->productSales as $item)
                    <tr>
                        <td style="padding:9px 12px; border:1px solid #e5e7eb; color:#1f2937;">{{ $item->name }}</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ $item->quantity }}</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($item->cashTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($item->cardTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($item->voucherTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937; font-weight:700;">{{ number_format($item->total, 2, ',', '.') }} RON</td>
                    </tr>
                    @endforeach
                    @if($locationReport->productSales->count() > 1)
                    <tr style="background-color:#f9fafb;">
                        <td style="padding:9px 12px; border:1px solid #e5e7eb; color:#374151; font-weight:600;" colspan="2">Subtotal produse</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:600;">{{ number_format($locationReport->productsCash, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:600;">{{ number_format($locationReport->productsCard, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:600;">{{ number_format($locationReport->productsVoucher, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:700;">{{ number_format($locationReport->productsTotal, 2, ',', '.') }} RON</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @endif

            {{-- Pachete vandute --}}
            @if($locationReport->packageSales->count() > 0)
            <h3 style="margin:0 0 8px 0; font-size:13px; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Pachete vândute</h3>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px; margin-bottom:20px;">
                <thead>
                    <tr style="background-color:#f9fafb;">
                        <th style="padding:9px 12px; text-align:left; border:1px solid #e5e7eb; color:#6b7280; font-weight:600;">Pachet</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Cant.</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Cash</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Card</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#6b7280; font-weight:600; white-space:nowrap;">Voucher</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:700; white-space:nowrap;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locationReport->packageSales as $item)
                    <tr>
                        <td style="padding:9px 12px; border:1px solid #e5e7eb; color:#1f2937;">{{ $item->name }}</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ $item->quantity }}</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($item->cashTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($item->cardTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937;">{{ number_format($item->voucherTotal, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#1f2937; font-weight:700;">{{ number_format($item->total, 2, ',', '.') }} RON</td>
                    </tr>
                    @endforeach
                    @if($locationReport->packageSales->count() > 1)
                    <tr style="background-color:#f9fafb;">
                        <td style="padding:9px 12px; border:1px solid #e5e7eb; color:#374151; font-weight:600;" colspan="2">Subtotal pachete</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:600;">{{ number_format($locationReport->packagesCash, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:600;">{{ number_format($locationReport->packagesCard, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:600;">{{ number_format($locationReport->packagesVoucher, 2, ',', '.') }} RON</td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #e5e7eb; color:#374151; font-weight:700;">{{ number_format($locationReport->packagesTotal, 2, ',', '.') }} RON</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @endif

            {{-- Total locatie --}}
            <div style="padding:12px 16px; background-color:#f0fdf4; border-radius:6px; border:1px solid #bbf7d0;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:14px; font-weight:700; color:#15803d;">Total {{ $locationReport->location->name }}</td>
                        <td style="text-align:right; font-size:13px; color:#166534; white-space:nowrap;">
                            Cash: <strong>{{ number_format($locationReport->grandCash, 2, ',', '.') }} RON</strong>
                            &nbsp;&nbsp;Card: <strong>{{ number_format($locationReport->grandCard, 2, ',', '.') }} RON</strong>
                            @if($locationReport->grandVoucher > 0)
                            &nbsp;&nbsp;Voucher: <strong>{{ number_format($locationReport->grandVoucher, 2, ',', '.') }} RON</strong>
                            @endif
                            &nbsp;&nbsp;Total: <strong>{{ number_format($locationReport->grandTotal, 2, ',', '.') }} RON</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
        @endforeach

        {{-- Total general (mai multe locatii) --}}
        @if($reportData->locationReports->count() > 1)
        <div style="margin-top:8px; margin-bottom:28px; padding:16px 20px; background-color:#eff6ff; border-radius:6px; border:1px solid #bfdbfe;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="font-size:15px; font-weight:700; color:#1e40af;">Total general</td>
                    <td style="text-align:right; font-size:14px; color:#1e3a8a; white-space:nowrap;">
                        Cash: <strong>{{ number_format($reportData->grandCash, 2, ',', '.') }} RON</strong>
                        &nbsp;&nbsp;Card: <strong>{{ number_format($reportData->grandCard, 2, ',', '.') }} RON</strong>
                        @if($reportData->grandVoucher > 0)
                        &nbsp;&nbsp;Voucher: <strong>{{ number_format($reportData->grandVoucher, 2, ',', '.') }} RON</strong>
                        @endif
                        &nbsp;&nbsp;
                        <span style="font-size:17px;">{{ number_format($reportData->grandTotal, 2, ',', '.') }} RON</span>
                    </td>
                </tr>
            </table>
        </div>
        @endif
@endif

        {{-- Rezervari de azi --}}
        @if($reportData->hasReservations)
        @php $today = \Carbon\Carbon::today(); @endphp
        <div style="margin-top:{{ $reportData->hasActivity ? '0' : '0' }}px;">
            <h2 style="margin:0 0 14px 0; font-size:17px; color:#1f2937; font-weight:700; border-bottom:2px solid #f59e0b; padding-bottom:8px;">
                Rezervări {{ $today->format('d.m.Y') }}
            </h2>

            @php $reservationsByLocation = $reportData->todayReservations->groupBy(fn($r) => $r->location->name ?? 'Necunoscut'); @endphp

            @foreach($reservationsByLocation as $locationName => $reservations)
            @if($reservationsByLocation->count() > 1)
            <p style="margin:0 0 8px 0; font-size:14px; font-weight:600; color:#92400e;">{{ $locationName }}</p>
            @endif
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px; margin-bottom:20px;">
                <thead>
                    <tr style="background-color:#fffbeb;">
                        <th style="padding:9px 12px; text-align:left; border:1px solid #fde68a; color:#92400e; font-weight:600; white-space:nowrap;">Ora</th>
                        <th style="padding:9px 12px; text-align:left; border:1px solid #fde68a; color:#92400e; font-weight:600;">Copil</th>
                        <th style="padding:9px 12px; text-align:left; border:1px solid #fde68a; color:#92400e; font-weight:600; white-space:nowrap;">Sala</th>
                        <th style="padding:9px 12px; text-align:left; border:1px solid #fde68a; color:#92400e; font-weight:600;">Pachet</th>
                        <th style="padding:9px 12px; text-align:right; border:1px solid #fde68a; color:#92400e; font-weight:600; white-space:nowrap;">Nr. copii</th>
                        <th style="padding:9px 12px; text-align:left; border:1px solid #fde68a; color:#92400e; font-weight:600;">Contact</th>
                        <th style="padding:9px 12px; text-align:left; border:1px solid #fde68a; color:#92400e; font-weight:600; white-space:nowrap;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $reservation)
                    <tr>
                        <td style="padding:9px 12px; border:1px solid #fde68a; color:#1f2937; font-weight:600; white-space:nowrap;">
                            {{ $reservation->reservation_time ? \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') : '—' }}
                        </td>
                        <td style="padding:9px 12px; border:1px solid #fde68a; color:#1f2937;">
                            {{ $reservation->child_name }}
                            @if($reservation->child_age)
                            <span style="color:#6b7280; font-size:12px;">({{ $reservation->child_age }} ani)</span>
                            @endif
                        </td>
                        <td style="padding:9px 12px; border:1px solid #fde68a; color:#1f2937; white-space:nowrap;">
                            {{ $reservation->birthdayHall?->name ?? '—' }}
                        </td>
                        <td style="padding:9px 12px; border:1px solid #fde68a; color:#1f2937;">
                            {{ $reservation->birthdayPackage?->name ?? '—' }}
                        </td>
                        <td style="padding:9px 12px; text-align:right; border:1px solid #fde68a; color:#1f2937;">
                            {{ $reservation->number_of_children }}
                        </td>
                        <td style="padding:9px 12px; border:1px solid #fde68a; color:#1f2937; font-size:13px;">
                            {{ $reservation->guardian_name }}<br>
                            <span style="color:#6b7280;">{{ $reservation->guardian_phone }}</span>
                        </td>
                        <td style="padding:9px 12px; border:1px solid #fde68a; white-space:nowrap;">
                            @php
                                $statusColors = [
                                    'confirmed' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                    'pending'   => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                                    'default'   => ['bg' => '#f3f4f6', 'text' => '#374151'],
                                ];
                                $statusLabels = [
                                    'confirmed' => 'Confirmat',
                                    'pending'   => 'În așteptare',
                                ];
                                $sc = $statusColors[$reservation->status] ?? $statusColors['default'];
                                $sl = $statusLabels[$reservation->status] ?? ucfirst($reservation->status ?? '—');
                            @endphp
                            <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600; background-color:{{ $sc['bg'] }}; color:{{ $sc['text'] }};">
                                {{ $sl }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
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
