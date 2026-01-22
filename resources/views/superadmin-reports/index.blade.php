@extends('layouts.app')

@section('title', 'Statistici Copii')
@section('page-title', 'Statistici Copii')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-bar text-indigo-600"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Statistici Copii - Număr Sesiuni</h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="text-sm text-gray-600 mr-2" for="perPage">Afișează</label>
                <select id="perPage" class="px-3 py-2 border border-gray-300 rounded-md">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="relative">
                <input id="searchInput" type="text" placeholder="Caută după numele copilului..." class="w-64 px-3 py-2 border border-gray-300 rounded-md pr-8">
                <i class="fas fa-search absolute right-2 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="name">
                        Nume Copil <span class="sort-ind" data-col="name"></span>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Locație
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="sessions_count">
                        Nr. Sesiuni Totale <span class="sort-ind" data-col="sessions_count"></span>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acțiuni
                    </th>
                </tr>
            </thead>
            <tbody id="tableBody" class="bg-white divide-y divide-gray-200"></tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-600" id="resultsInfo"></div>
        <div class="flex items-center gap-2">
            <button id="prevPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înapoi</button>
            <span class="text-sm text-gray-600" id="pageInfo"></span>
            <button id="nextPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înainte</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let state = {
        page: 1,
        per_page: 10,
        sort_by: 'sessions_count',
        sort_dir: 'desc',
        search: ''
    };
    let searchTimeout;

    function loadData() {
        const params = new URLSearchParams({
            page: state.page,
            per_page: state.per_page,
            sort_by: state.sort_by,
            sort_dir: state.sort_dir,
            search: state.search
        });

        fetch(`/reports/children/data?${params}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderTable(data.data);
                    updatePagination(data.meta);
                    updateSortIndicators();
                } else {
                    console.error('Error:', data.message);
                }
            })
            .catch(err => {
                console.error('Error loading data:', err);
            });
    }

    function renderTable(rows) {
        const tbody = document.getElementById('tableBody');
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Nu există copii înregistrați</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(row => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-child text-sky-600 text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900">${row.name}</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${row.location_name}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium ${
                        row.sessions_count > 10 
                            ? 'bg-green-100 text-green-800' 
                            : row.sessions_count > 5 
                                ? 'bg-blue-100 text-blue-800' 
                                : row.sessions_count > 0
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-gray-100 text-gray-800'
                    }">
                        <i class="fas fa-play-circle mr-1"></i>
                        ${row.sessions_count}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <a href="/children/${row.id}" class="text-sky-600 hover:text-sky-800">
                        <i class="fas fa-eye mr-1"></i> Vezi detalii
                    </a>
                </td>
            </tr>
        `).join('');
    }

    function updatePagination(meta) {
        const start = meta.total === 0 ? 0 : (meta.page - 1) * meta.per_page + 1;
        const end = Math.min(meta.page * meta.per_page, meta.total);
        document.getElementById('resultsInfo').textContent = 
            `Afișare ${start} - ${end} din ${meta.total}`;
        document.getElementById('pageInfo').textContent = `Pagina ${meta.page} din ${Math.max(1, meta.total_pages)}`;
        document.getElementById('prevPage').disabled = meta.page === 1;
        document.getElementById('nextPage').disabled = meta.page >= meta.total_pages;
    }

    function updateSortIndicators() {
        document.querySelectorAll('.sort-ind').forEach(ind => {
            ind.textContent = '';
        });
        const activeInd = document.querySelector(`.sort-ind[data-col="${state.sort_by}"]`);
        if (activeInd) {
            activeInd.textContent = state.sort_dir === 'asc' ? ' ▲' : ' ▼';
        }
    }

    // Event listeners
    document.getElementById('perPage').addEventListener('change', (e) => {
        state.per_page = parseInt(e.target.value);
        state.page = 1;
        loadData();
    });

    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            state.search = e.target.value;
            state.page = 1;
            loadData();
        }, 500);
    });

    document.querySelectorAll('[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.getAttribute('data-sort');
            if (state.sort_by === col) {
                state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort_by = col;
                state.sort_dir = 'desc';
            }
            loadData();
        });
    });

    document.getElementById('prevPage').addEventListener('click', () => {
        if (state.page > 1) {
            state.page--;
            loadData();
        }
    });

    document.getElementById('nextPage').addEventListener('click', () => {
        state.page++;
        loadData();
    });

    // Initial load
    loadData();
</script>
@endsection


