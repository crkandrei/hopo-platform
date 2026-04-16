@extends('layouts.app')

@section('title', 'Conformitate GDPR/T&C')
@section('page-title', 'Conformitate GDPR/T&C')

@section('content')
<div class="space-y-4 md:space-y-6">

    {{-- Carduri statistici --}}
    <div id="summary-cards" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total părinți</div>
            <div id="stat-total" class="text-3xl font-bold text-gray-800">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Au acceptat ambele</div>
            <div id="stat-both" class="text-3xl font-bold text-green-600">—</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">În așteptare</div>
            <div id="stat-pending" class="text-3xl font-bold text-red-600">—</div>
        </div>
    </div>

    {{-- Filtre + Export --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Status T&C</label>
                    <select id="filter-terms" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="all">Toți</option>
                        <option value="accepted">Acceptat</option>
                        <option value="not_accepted">Neacceptat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Status GDPR</label>
                    <select id="filter-gdpr" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="all">Toți</option>
                        <option value="accepted">Acceptat</option>
                        <option value="not_accepted">Neacceptat</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="btn-apply" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-medium">
                        <i class="fas fa-filter mr-1"></i>Aplică filtre
                    </button>
                </div>
            </div>
            <div>
                <button id="btn-export-pdf" class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-medium">
                    <i class="fas fa-file-pdf mr-1"></i>Export PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="name">
                            Nume <i class="fas fa-sort text-gray-300 ml-1"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Telefon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="terms_accepted_at">
                            T&C Acceptat la <i class="fas fa-sort text-gray-300 ml-1"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ver. T&C</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="gdpr_accepted_at">
                            GDPR Acceptat la <i class="fas fa-sort text-gray-300 ml-1"></i>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ver. GDPR</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="created_at">
                            Înregistrat <i class="fas fa-sort text-gray-300 ml-1"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-gray-100">
                    <tr><td colspan="7" class="text-center py-8 text-gray-400">Se încarcă...</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Paginare --}}
        <div class="px-4 py-3 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span>Rânduri per pagină:</span>
                <select id="per-page" class="border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div id="pagination-info" class="text-sm text-gray-500 text-center"></div>
            <div class="flex gap-2">
                <button id="btn-prev" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-40" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="btn-next" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-40" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const dataUrl  = '{{ route("reports.gdpr-compliance.data") }}';
    const pdfUrl   = '{{ route("reports.gdpr-compliance.pdf") }}';

    let state = { page: 1, perPage: 10, termsStatus: 'all', gdprStatus: 'all', sortBy: 'name', sortDir: 'asc' };

    function badgeHtml(dateStr) {
        if (!dateStr) return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Neacceptat</span>';
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">${escHtml(dateStr)}</span>`;
    }

    function renderRows(data) {
        const tbody = document.getElementById('table-body');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-400">Niciun părinte găsit</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(g => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">${escHtml(g.name)}</td>
                <td class="px-4 py-3 text-gray-600">${escHtml(g.phone)}</td>
                <td class="px-4 py-3">${badgeHtml(g.terms_accepted_at)}</td>
                <td class="px-4 py-3 text-gray-500">${escHtml(g.terms_version ?? '—')}</td>
                <td class="px-4 py-3">${badgeHtml(g.gdpr_accepted_at)}</td>
                <td class="px-4 py-3 text-gray-500">${escHtml(g.gdpr_version ?? '—')}</td>
                <td class="px-4 py-3 text-gray-500">${escHtml(g.created_at)}</td>
            </tr>
        `).join('');
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '—';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderPagination(meta) {
        document.getElementById('pagination-info').textContent =
            `Pagina ${meta.page} din ${meta.total_pages} (${meta.total} total)`;
        document.getElementById('btn-prev').disabled = meta.page <= 1;
        document.getElementById('btn-next').disabled = meta.page >= meta.total_pages;
    }

    function renderSummary(summary) {
        document.getElementById('stat-total').textContent   = summary.total;
        document.getElementById('stat-both').textContent    = summary.both_accepted;
        document.getElementById('stat-pending').textContent = summary.pending;
    }

    function load() {
        const params = new URLSearchParams({
            page:         state.page,
            per_page:     state.perPage,
            terms_status: state.termsStatus,
            gdpr_status:  state.gdprStatus,
            sort_by:      state.sortBy,
            sort_dir:     state.sortDir,
        });

        fetch(`${dataUrl}?${params}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                document.getElementById('table-body').innerHTML =
                    '<tr><td colspan="7" class="text-center py-8 text-red-500">Eroare la încărcarea datelor</td></tr>';
                return;
            }
            renderRows(json.data);
            renderPagination(json.meta);
            renderSummary(json.summary);
        })
        .catch(() => {
            document.getElementById('table-body').innerHTML =
                '<tr><td colspan="7" class="text-center py-8 text-red-500">Eroare de rețea. Reîncărcați pagina.</td></tr>';
        });
    }

    // Filtre
    document.getElementById('btn-apply').addEventListener('click', () => {
        state.page        = 1;
        state.termsStatus = document.getElementById('filter-terms').value;
        state.gdprStatus  = document.getElementById('filter-gdpr').value;
        load();
    });

    // Export PDF
    document.getElementById('btn-export-pdf').addEventListener('click', () => {
        const params = new URLSearchParams({
            terms_status: state.termsStatus,
            gdpr_status:  state.gdprStatus,
        });
        window.open(`${pdfUrl}?${params}`, '_blank');
    });

    // Paginare
    document.getElementById('btn-prev').addEventListener('click', () => { state.page--; load(); });
    document.getElementById('btn-next').addEventListener('click', () => { state.page++; load(); });
    document.getElementById('per-page').addEventListener('change', e => {
        state.perPage = parseInt(e.target.value);
        state.page = 1;
        load();
    });

    // Sortare
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.sort;
            if (state.sortBy === col) {
                state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sortBy = col;
                state.sortDir = 'asc';
            }
            state.page = 1;
            load();
        });
    });

    load();
})();
</script>
@endsection
