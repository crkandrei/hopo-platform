<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport Conformitate GDPR/T&C — {{ $location->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 24px; }
        h1 { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { color: #555; font-size: 12px; margin-bottom: 20px; }
        .summary { display: flex; gap: 24px; margin-bottom: 20px; }
        .summary-card { border: 1px solid #ddd; border-radius: 6px; padding: 10px 16px; min-width: 120px; }
        .summary-card .label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 0.05em; }
        .summary-card .value { font-size: 20px; font-weight: bold; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; border: 1px solid #e5e7eb; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
        td { padding: 5px 8px; border: 1px solid #e5e7eb; vertical-align: middle; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge-ok { color: #065f46; background: #d1fae5; padding: 1px 6px; border-radius: 10px; font-size: 10px; white-space: nowrap; }
        .badge-no { color: #991b1b; background: #fee2e2; padding: 1px 6px; border-radius: 10px; font-size: 10px; white-space: nowrap; }
        .print-btn { display: inline-block; margin-bottom: 20px; padding: 8px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Printează / Salvează PDF</button>

    <h1>Raport Conformitate GDPR/T&amp;C</h1>
    <div class="subtitle">
        {{ $location->name }} &nbsp;·&nbsp; Generat la {{ $generatedAt }}
        @if($filters['terms_status'] !== 'all' || $filters['gdpr_status'] !== 'all')
            &nbsp;·&nbsp; Filtre active:
            @if($filters['terms_status'] !== 'all') T&amp;C: {{ $filters['terms_status'] === 'accepted' ? 'acceptat' : 'neacceptat' }} @endif
            @if($filters['gdpr_status'] !== 'all') GDPR: {{ $filters['gdpr_status'] === 'accepted' ? 'acceptat' : 'neacceptat' }} @endif
        @endif
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Total părinți</div>
            <div class="value">{{ $summary['total'] }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Au acceptat ambele</div>
            <div class="value" style="color:#065f46">{{ $summary['both_accepted'] }}</div>
        </div>
        <div class="summary-card">
            <div class="label">În așteptare</div>
            <div class="value" style="color:#991b1b">{{ $summary['pending'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nume</th>
                <th>Telefon</th>
                <th>T&amp;C Acceptat la</th>
                <th>Ver. T&amp;C</th>
                <th>GDPR Acceptat la</th>
                <th>Ver. GDPR</th>
                <th>Înregistrat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($guardians as $i => $g)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $g->name }}</td>
                <td>{{ $g->phone ?? '—' }}</td>
                <td>
                    @if($g->terms_accepted_at)
                        <span class="badge-ok">{{ $g->terms_accepted_at->format('d.m.Y H:i') }}</span>
                    @else
                        <span class="badge-no">Neacceptat</span>
                    @endif
                </td>
                <td>{{ $g->terms_version ?? '—' }}</td>
                <td>
                    @if($g->gdpr_accepted_at)
                        <span class="badge-ok">{{ $g->gdpr_accepted_at->format('d.m.Y H:i') }}</span>
                    @else
                        <span class="badge-no">Neacceptat</span>
                    @endif
                </td>
                <td>{{ $g->gdpr_version ?? '—' }}</td>
                <td>{{ $g->created_at->format('d.m.Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#666; padding:20px;">Niciun părinte găsit</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
