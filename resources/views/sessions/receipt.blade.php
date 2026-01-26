<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon Nefiscal - Sesiune #{{ $session->id }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon-32x32.png') }}?v=3">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            @page {
                size: 80mm auto;
                margin: 2mm;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            padding: 20px;
            background: #f5f5f5;
            color: #000;
        }
        
        .receipt-container {
            max-width: 227px;
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 10px;
        }
        
        @media print {
            .receipt-container {
                max-width: 100%;
                padding: 2mm;
                box-shadow: none;
            }
        }
        
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .print-button button {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .print-button button:hover {
            background: #059669;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        
        .receipt-header h1 {
            font-size: 16px;
            font-weight: 900;
            margin-bottom: 2px;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .receipt-body {
            margin-bottom: 8px;
        }
        
        .receipt-row {
            margin-bottom: 4px;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .receipt-row .label {
            font-weight: 700;
            display: inline-block;
            min-width: 90px;
        }
        
        .receipt-row .value {
            display: inline-block;
            font-weight: 700;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        
        .total-section {
            border-top: 1px solid #000;
            padding-top: 6px;
            margin-top: 6px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 900;
            padding: 3px 0;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #000;
            font-size: 9px;
            color: #000;
            line-height: 1.4;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="no-print print-button">
        <button onclick="window.print()">
            🖨️ Printează Bon
        </button>
    </div>
    
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>{{ $session->location->name ?? 'Loc de Joacă' }}</h1>
        </div>
        
        <div class="receipt-body">
            <div class="receipt-row">
                <span class="label">Data și ora:</span>
                <span class="value">{{ $session->ended_at->format('d.m.Y H:i') }}</span>
            </div>
            
            <div class="divider"></div>
            
            <div class="receipt-row">
                <span class="label">Nume copil:</span>
                <span class="value">{{ $session->child ? $session->child->first_name . ' ' . $session->child->last_name : '-' }}</span>
            </div>
            
            @if($session->child && $session->child->guardian)
            <div class="receipt-row">
                <span class="label">Nume părinte:</span>
                <span class="value">{{ $session->child->guardian->name }}</span>
            </div>
            @endif
            
            <div class="divider"></div>
            
            <div class="receipt-row">
                <span class="label">Început sesiune:</span>
                <span class="value">{{ $session->started_at->format('H:i') }}</span>
            </div>
            
            <div class="receipt-row">
                <span class="label">Sfârșit sesiune:</span>
                <span class="value">{{ $session->ended_at->format('H:i') }}</span>
            </div>
            
            <div class="receipt-row">
                <span class="label">Durată efectivă:</span>
                <span class="value">{{ $session->getFormattedDuration() }}</span>
            </div>
            
            @if($session->price_per_hour_at_calculation)
            <div class="receipt-row">
                <span class="label">Preț/oră:</span>
                <span class="value" style="font-weight: 700;">{{ number_format($session->price_per_hour_at_calculation, 2, '.', '') }} RON</span>
            </div>
            @endif
            
            <div class="divider"></div>
            
            @if($session->products && $session->products->count() > 0)
            <div class="receipt-row" style="font-weight: 700; margin-bottom: 3px;">
                <span>Produse:</span>
            </div>
            @foreach($session->products as $sessionProduct)
            <div class="receipt-row" style="padding-left: 8px; margin-bottom: 2px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: 700;">{{ $sessionProduct->product->name ?? 'Produs' }} x{{ $sessionProduct->quantity }}</span>
                    <span style="font-weight: 700;">{{ number_format($sessionProduct->total_price, 2, '.', '') }} RON</span>
                </div>
            </div>
            @endforeach
            <div class="divider"></div>
            @endif
            
            <div class="total-section">
                @if($session->products && $session->products->count() > 0)
                <div class="total-row" style="font-size: 11px; font-weight: 600; padding: 2px 0;">
                    <span>Timp de joacă:</span>
                    <span>{{ $session->getFormattedPrice() }}</span>
                </div>
                <div class="total-row" style="font-size: 11px; font-weight: 600; padding: 2px 0;">
                    <span>Produse:</span>
                    <span>{{ number_format($session->getProductsTotalPrice(), 2, '.', '') }} RON</span>
                </div>
                @endif
                <div class="total-row">
                    <span>TOTAL DE PLATĂ:</span>
                    <span>{{ $session->getFormattedTotalPrice() }}</span>
                </div>
            </div>
        </div>
        
        <div class="receipt-footer">
            <div>Mulțumim pentru vizită!</div>
            <div style="margin-top: 4px;">Acest document este un bon nefiscal</div>
        </div>
    </div>
    
    <script>
        // Print-ul este controlat din JavaScript prin iframe
        // Nu mai este nevoie de auto-print aici
    </script>
</body>
</html>
