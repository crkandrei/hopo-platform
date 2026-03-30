<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervare zi de naștere — {{ $action === 'confirm' ? 'Confirmată' : 'Respinsă' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; color: #1f2937; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); max-width: 520px; width: 100%; padding: 48px 40px; text-align: center; }
        .icon { font-size: 64px; margin-bottom: 20px; animation: pop .4s ease-out; display: block; }
        @keyframes pop { 0% { transform: scale(0.5); opacity: 0; } 80% { transform: scale(1.1); } 100% { transform: scale(1); opacity: 1; } }
        .title { font-size: 26px; font-weight: 800; margin-bottom: 8px; }
        .subtitle { font-size: 15px; color: #6b7280; margin-bottom: 32px; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 32px; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-pending   { background: #fef3c7; color: #92400e; }
        .detail-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px 24px; text-align: left; margin-bottom: 28px; }
        .detail-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .detail-row:not(:last-child) { border-bottom: 1px solid #e5e7eb; }
        .detail-label { color: #6b7280; font-weight: 600; }
        .detail-value { color: #111827; font-weight: 500; }
        .note { font-size: 13px; color: #9ca3af; margin-bottom: 24px; }
        .admin-link { display: inline-block; background: #6366f1; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; }
        .admin-link:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <div class="card">

        @if(!$processed)
            {{-- Already processed --}}
            <span class="icon">ℹ️</span>
            <h1 class="title" style="color:#374151;">Rezervare deja procesată</h1>
            <p class="subtitle">Această rezervare a fost deja {{ $reservation->status === 'confirmed' ? 'confirmată' : 'respinsă' }} anterior.</p>
        @elseif($action === 'confirm')
            <span class="icon">✅</span>
            <h1 class="title" style="color:#059669;">Rezervare confirmată!</h1>
            <p class="subtitle">Rezervarea pentru <strong>{{ $reservation->child_name }}</strong> a fost confirmată cu succes.</p>
        @else
            <span class="icon">❌</span>
            <h1 class="title" style="color:#dc2626;">Rezervare respinsă</h1>
            <p class="subtitle">Rezervarea pentru <strong>{{ $reservation->child_name }}</strong> a fost respinsă.</p>
        @endif

        @php
            $statusLabels = ['pending' => 'În așteptare', 'confirmed' => 'Confirmată', 'cancelled' => 'Respinsă'];
            $statusClasses = ['pending' => 'status-pending', 'confirmed' => 'status-confirmed', 'cancelled' => 'status-cancelled'];
            $currentStatus = $reservation->status;
        @endphp

        <div>
            <span class="status-badge {{ $statusClasses[$currentStatus] ?? 'status-pending' }}">
                Status: {{ $statusLabels[$currentStatus] ?? $currentStatus }}
            </span>
        </div>

        <div class="detail-box">
            <div class="detail-row">
                <span class="detail-label">Copil</span>
                <span class="detail-value">{{ $reservation->child_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Data</span>
                <span class="detail-value">{{ $reservation->reservation_date->format('d.m.Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Ora</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Sală</span>
                <span class="detail-value">{{ $reservation->birthdayHall->name ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Nr. copii</span>
                <span class="detail-value">{{ $reservation->number_of_children }}</span>
            </div>
            @if($reservation->number_of_adults !== null)
            <div class="detail-row">
                <span class="detail-label">Nr. adulți</span>
                <span class="detail-value">{{ $reservation->number_of_adults }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Tutore</span>
                <span class="detail-value">{{ $reservation->guardian_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Telefon</span>
                <span class="detail-value">{{ $reservation->guardian_phone }}</span>
            </div>
        </div>

        @if(!$processed)
            <p class="note">Acțiunea a fost înregistrată. Tutorele va fi informat separat.</p>
        @endif

        @auth
            <a href="{{ route('birthday-reservations.index') }}" class="admin-link">
                Deschide panoul de administrare
            </a>
        @endauth

    </div>
</body>
</html>
