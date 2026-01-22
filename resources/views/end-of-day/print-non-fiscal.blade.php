<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport Nefiscal - Final de Zi</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 2px;
                font-family: 'Courier New', monospace;
                font-size: 9pt;
            }
            .no-print {
                display: none !important;
            }
        }
        
        @media screen {
            body {
                font-family: 'Courier New', monospace;
                font-size: 12pt;
                padding: 10px;
                max-width: 80mm;
                margin: 0 auto;
                background: #f5f5f5;
            }
        }
        
        body {
            margin: 0;
            padding: 2px;
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            line-height: 1.1;
        }
        
        .report-content {
            text-align: center;
            white-space: pre-line;
        }
        
        .report-line {
            margin: 0;
            padding: 0;
            font-weight: bold;
        }
        
        .report-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 0;
            margin-top: 0;
        }
        
        .report-separator {
            border-top: 1px dashed #000;
            margin: 1px 0;
            padding: 0;
        }
        
        .no-print {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
        }
        
        .no-print button {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14pt;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .no-print button:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <div class="report-content">
        <div class="report-line report-title">RAPORT FINAL DE ZI</div>
        <div class="report-line">{{ $date }}</div>
        <div class="report-separator"></div>
        <div class="report-line report-title" style="font-size: 9pt;">NUMAR COPII</div>
        <div class="report-line">Total: {{ $regularSessions }}</div>
        <div class="report-separator"></div>
        <div class="report-line">Sesiuni Total: {{ $totalBilledHours }} {{ number_format($totalSessionsValue, 2, ',', '.') }} lei</div>
        @if($totalVoucherHours && $totalVoucherHours !== '0h' && $totalVoucherHours !== '0m')
        <div class="report-line">Total Voucher: {{ $totalVoucherHours }}</div>
        @endif
        @if(count($productsGrouped) > 0)
        <div class="report-separator"></div>
        <div class="report-line report-title" style="font-size: 9pt;">PRODUSE</div>
        @foreach($productsGrouped as $product)
        <div class="report-line">{{ $product['name'] }}, {{ $product['quantity'] }} bucati, {{ number_format($product['total'], 2, ',', '.') }} LEI</div>
        @endforeach
        <div class="report-separator"></div>
        <div class="report-line">Total Produse: {{ number_format($totalProductsValue, 2, ',', '.') }} lei</div>
        @endif
        @if($cashTotal > 0 || $cardTotal > 0 || $voucherTotal > 0)
        <div class="report-separator"></div>
        <div class="report-line report-title" style="font-size: 9pt;">PLĂȚI</div>
        @if($cashTotal > 0)
        <div class="report-line">Cash: {{ number_format($cashTotal, 2, ',', '.') }} lei</div>
        @endif
        @if($cardTotal > 0)
        <div class="report-line">Card: {{ number_format($cardTotal, 2, ',', '.') }} lei</div>
        @endif
        @if($voucherTotal > 0)
        <div class="report-line">Voucher: {{ number_format($voucherTotal, 2, ',', '.') }} lei</div>
        @endif
        @endif
        <div class="report-separator"></div>
        <div class="report-line">Total General: {{ number_format($totalSessionsValue + $totalProductsValue, 2, ',', '.') }} lei</div>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()">Imprimă</button>
        <button onclick="window.close()" style="background: #6b7280; margin-left: 10px;">Închide</button>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 250);
        };
    </script>
</body>
</html>

