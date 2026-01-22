@extends('layouts.app')

@section('title', 'Raport General')
@section('page-title', 'Raport General')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover">
        <div class="p-3 md:p-4 lg:p-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="w-full md:w-auto">
                        <label class="block text-sm text-gray-600 mb-1">Data start</label>
                        <input type="date" id="reportStart" class="w-full md:w-auto px-3 py-2.5 md:py-2 border border-gray-300 rounded-md text-base md:text-sm" />
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-sm text-gray-600 mb-1">Data stop</label>
                        <input type="date" id="reportEnd" class="w-full md:w-auto px-3 py-2.5 md:py-2 border border-gray-300 rounded-md text-base md:text-sm" />
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-sm text-transparent mb-1">&nbsp;</label>
                        <button id="loadReport" class="w-full md:w-auto px-4 py-2.5 md:py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-base md:text-sm font-medium">
                            <i class="fas fa-sync-alt mr-2"></i>Încarcă Raport
                        </button>
                    </div>
                </div>
                <div class="w-full md:w-auto">
                    <button id="exportPdf" class="w-full md:w-auto px-4 py-2.5 md:py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 text-base md:text-sm font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Info -->
    <div id="periodInfo" class="text-center text-gray-600 text-sm hidden">
        Raport pentru perioada: <span id="periodText" class="font-semibold"></span>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Total Hours Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Ore Jucate</p>
                    <p id="totalHours" class="text-2xl md:text-3xl font-bold text-indigo-600">-</p>
                    <p id="avgDuration" class="text-xs text-gray-500 mt-1">Media: -</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-indigo-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Sessions Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 card-hover">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Sesiuni</p>
                    <p id="totalSessions" class="text-2xl md:text-3xl font-bold text-blue-600">-</p>
                    <div id="sessionsBreakdown" class="text-xs text-gray-500 mt-1 space-y-0.5">
                        <div>Total: <span id="normalSessions">-</span></div>
                    </div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-play-circle text-blue-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Products Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Produse Vândute</p>
                    <p id="totalProducts" class="text-2xl md:text-3xl font-bold text-purple-600">-</p>
                    <p id="productsRevenue" class="text-xs text-gray-500 mt-1">Valoare: -</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-purple-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Sales Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 card-hover">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Vânzări</p>
                    <p id="totalSales" class="text-2xl md:text-3xl font-bold text-emerald-600">-</p>
                    <div id="salesBreakdown" class="text-xs text-gray-500 mt-1 space-y-0.5">
                        <div>Cash: <span id="cashTotal">-</span></div>
                        <div>Card: <span id="cardTotal">-</span></div>
                        <div>Voucher: <span id="voucherTotal">-</span></div>
                    </div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-emerald-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover">
        <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                    <i class="fas fa-trophy text-purple-600 text-sm md:text-base"></i>
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-900">Top Produse Vândute</h2>
            </div>
        </div>
        <div class="p-4 md:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produs</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantitate</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valoare</th>
                        </tr>
                    </thead>
                    <tbody id="topProductsBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Se încarcă...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    let currentReportData = null;

    async function apiCall(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                ...options.headers
            },
            credentials: 'same-origin'
        });
        return response.json();
    }

    // Initialize date inputs with current month
    function initDateDefaults() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        const formatDate = (date) => {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        };
        
        document.getElementById('reportStart').value = formatDate(firstDay);
        document.getElementById('reportEnd').value = formatDate(today);
    }

    async function loadReport() {
        const start = document.getElementById('reportStart').value;
        const end = document.getElementById('reportEnd').value;
        
        if (!start || !end) {
            alert('Te rog selectează perioada pentru raport.');
            return;
        }
        
        try {
            const qs = new URLSearchParams({ start, end });
            const response = await apiCall('/reports/general/data?' + qs.toString());
            
            if (response.success) {
                currentReportData = response;
                renderReport(response);
            } else {
                console.error('Eroare:', response.message);
                alert('Eroare la încărcarea raportului: ' + (response.message || 'Eroare necunoscută'));
            }
        } catch (error) {
            console.error('Eroare:', error);
            alert('Eroare la încărcarea raportului.');
        }
    }

    function renderReport(data) {
        // Period info
        document.getElementById('periodInfo').classList.remove('hidden');
        document.getElementById('periodText').textContent = `${data.period.start} - ${data.period.end}`;
        
        // Hours card
        document.getElementById('totalHours').textContent = data.hours.formatted;
        document.getElementById('avgDuration').textContent = `Media: ${data.hours.avg_per_session}`;
        
        // Sessions card
        document.getElementById('totalSessions').textContent = data.sessions.total;
        document.getElementById('normalSessions').textContent = data.sessions.normal;
        
        // Products card
        document.getElementById('totalProducts').textContent = data.products.total_sold;
        document.getElementById('productsRevenue').textContent = `Valoare: ${data.products.total_revenue.toFixed(2)} RON`;
        
        // Sales card
        document.getElementById('totalSales').textContent = `${data.sales.total.toFixed(2)} RON`;
        document.getElementById('cashTotal').textContent = `${data.sales.cash.toFixed(2)} RON`;
        document.getElementById('cardTotal').textContent = `${data.sales.card.toFixed(2)} RON`;
        document.getElementById('voucherTotal').textContent = `${data.sales.voucher.toFixed(2)} RON`;
        
        // Top products table
        renderTopProducts(data.products.top_products);
    }

    function renderTopProducts(products) {
        const tbody = document.getElementById('topProductsBody');
        
        if (!products || products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                        Nu au fost vândute produse în această perioadă
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = products.map((product, index) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${index + 1}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-box text-purple-600 text-xs"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900">${product.name}</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-medium">${product.quantity}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">${product.total.toFixed(2)} RON</td>
            </tr>
        `).join('');
    }

    function exportPdf() {
        if (!currentReportData) {
            alert('Te rog încarcă mai întâi raportul.');
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
            compress: true
        });
        
        const data = currentReportData;
        
        // Remove diacritics helper
        function removeDiacritics(text) {
            if (!text) return text;
            return text
                .replace(/ă/g, 'a').replace(/Ă/g, 'A')
                .replace(/â/g, 'a').replace(/Â/g, 'A')
                .replace(/î/g, 'i').replace(/Î/g, 'I')
                .replace(/ș/g, 's').replace(/Ș/g, 'S')
                .replace(/ț/g, 't').replace(/Ț/g, 'T');
        }
        
        doc.setFont('helvetica');
        
        // Title
        doc.setFontSize(18);
        doc.setFont('helvetica', 'bold');
        doc.text('RAPORT GENERAL', 105, 20, { align: 'center' });
        
        // Period
        doc.setFontSize(12);
        doc.setFont('helvetica', 'normal');
        doc.text(`Perioada: ${data.period.start} - ${data.period.end}`, 105, 30, { align: 'center' });
        
        // Summary section
        let yPos = 45;
        
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text('SUMAR', 14, yPos);
        yPos += 8;
        
        // Summary table
        doc.autoTable({
            startY: yPos,
            head: [['Indicator', 'Valoare']],
            body: [
                ['Total ore jucate', removeDiacritics(data.hours.formatted)],
                ['Durata medie sesiune', data.hours.avg_per_session],
                ['Total sesiuni', data.sessions.total.toString()],
                ['Total produse vandute', data.products.total_sold.toString()],
                ['Valoare produse', data.products.total_revenue.toFixed(2) + ' RON'],
                ['TOTAL VANZARI', data.sales.total.toFixed(2) + ' RON'],
                ['  - Cash', data.sales.cash.toFixed(2) + ' RON'],
                ['  - Card', data.sales.card.toFixed(2) + ' RON'],
                ['  - Voucher', data.sales.voucher.toFixed(2) + ' RON'],
            ],
            theme: 'striped',
            styles: {
                font: 'helvetica',
                fontSize: 10,
                cellPadding: 3,
            },
            headStyles: {
                fillColor: [99, 102, 241],
                textColor: [255, 255, 255],
                fontStyle: 'bold',
            },
            columnStyles: {
                0: { cellWidth: 80 },
                1: { cellWidth: 60, halign: 'right' },
            },
            margin: { left: 14, right: 14 },
        });
        
        yPos = doc.lastAutoTable.finalY + 15;
        
        // Top products section
        if (data.products.top_products && data.products.top_products.length > 0) {
            doc.setFontSize(14);
            doc.setFont('helvetica', 'bold');
            doc.text('TOP PRODUSE VANDUTE', 14, yPos);
            yPos += 8;
            
            const productRows = data.products.top_products.map((p, i) => [
                (i + 1).toString(),
                removeDiacritics(p.name),
                p.quantity.toString(),
                p.total.toFixed(2) + ' RON'
            ]);
            
            doc.autoTable({
                startY: yPos,
                head: [['#', 'Produs', 'Cantitate', 'Valoare']],
                body: productRows,
                theme: 'striped',
                styles: {
                    font: 'helvetica',
                    fontSize: 9,
                    cellPadding: 2,
                },
                headStyles: {
                    fillColor: [147, 51, 234],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold',
                },
                columnStyles: {
                    0: { cellWidth: 15, halign: 'center' },
                    1: { cellWidth: 80 },
                    2: { cellWidth: 30, halign: 'center' },
                    3: { cellWidth: 40, halign: 'right' },
                },
                margin: { left: 14, right: 14 },
            });
        }
        
        // Footer
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(128);
            doc.text(
                `Generat la: ${new Date().toLocaleString('ro-RO')} | Pagina ${i} din ${pageCount}`,
                105,
                290,
                { align: 'center' }
            );
        }
        
        // Save PDF
        const startDate = document.getElementById('reportStart').value;
        const endDate = document.getElementById('reportEnd').value;
        const fileName = `raport_general_${startDate}_${endDate}.pdf`;
        doc.save(fileName);
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        initDateDefaults();
        loadReport();
        
        document.getElementById('loadReport').addEventListener('click', loadReport);
        document.getElementById('exportPdf').addEventListener('click', exportPdf);
    });
</script>
@endsection

