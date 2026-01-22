@extends('layouts.app')

@section('title', 'Copii & Sesiuni')
@section('page-title', 'Copii & Sesiuni')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Copii & Sesiuni 👶</h1>
                <p class="text-gray-600 text-lg">Gestionați copiii și sesiunile lor active</p>
            </div>
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin() || Auth::user()->isStaff())
            <button id="open-add-child-modal" 
               class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-6 py-3 rounded-lg hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Adaugă Copil Nou
            </button>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Copii</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $children->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Înregistrați</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-child text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Sesiuni Active</p>
                    <p class="text-3xl font-bold text-green-600">{{ \App\Models\PlaySession::where('location_id', Auth::user()->location->id)->whereNull('ended_at')->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">În desfășurare</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-play-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Copii</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $children->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">În sistem</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Children Table -->
    <div id="children-table-root" class="bg-white rounded-lg shadow overflow-hidden" data-children-data-url="{{ route('children.data') }}" data-guardians-search-url="{{ route('guardians.search') }}" data-guardians-store-url="{{ route('guardians.store') }}" data-can-edit="{{ (Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin() || Auth::user()->isStaff()) ? '1' : '0' }}" data-has-validation-errors="{{ $errors->any() ? '1' : '0' }}">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h2 class="text-xl font-bold text-gray-900">Lista Copiilor</h2>
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="text-sm text-gray-600 mr-2" for="childrenPerPage">Afișează</label>
                        <select id="childrenPerPage" class="px-3 py-2 border border-gray-300 rounded-md">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="relative">
                        <input id="childrenSearchInput" type="text" placeholder="Caută (copil, părinte, telefon, cod)" class="w-64 px-3 py-2 border border-gray-300 rounded-md pr-8">
                        <i class="fas fa-search absolute right-2 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="child_name">Copil <span class="sort-ind" data-col="child_name"></span></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="guardian_name">Părinte <span class="sort-ind" data-col="guardian_name"></span></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesiune</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acțiuni</th>
                    </tr>
                </thead>
                <tbody id="childrenTableBody" class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
            <div class="text-sm text-gray-600" id="childrenResultsInfo"></div>
            <div class="flex items-center gap-2">
                <button id="childrenPrevPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înapoi</button>
                <span id="childrenPageLabel" class="text-sm text-gray-700"></span>
                <button id="childrenNextPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înainte</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
@if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin() || Auth::user()->isStaff())
@php
    $guardians = \App\Models\Guardian::where('location_id', Auth::user()->location->id)->orderBy('name')->get();
@endphp
<div id="add-child-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="add-child-overlay" class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Adaugă Copil</h3>
                <button id="close-add-child-modal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('children.store') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nume complet <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror" placeholder="Ex: Andrei Popescu" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="guardian_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Părinte/Tutore <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="guardian_id" name="guardian_id" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('guardian_id') border-red-500 @enderror" required>
                                <option value="">Selectează părinte</option>
                                @foreach($guardians as $guardian)
                                    <option value="{{ $guardian->id }}" {{ old('guardian_id') == $guardian->id ? 'selected' : '' }}>{{ $guardian->name }} @if($guardian->phone) ({{ $guardian->phone }}) @endif</option>
                                @endforeach
                            </select>
                            <button type="button" id="open-add-guardian" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-2 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 z-10" title="Adaugă părinte">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        @error('guardian_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Note (opțional)
                        </label>
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror" placeholder="Alergii, preferințe alimentare, observații medicale...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Maxim 1000 caractere</p>
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <button type="button" id="cancel-add-child" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Anulează</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Salvează</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</div>

<script>
    (function() {
        // State for AJAX table
        let childrenState = {
            page: 1,
            per_page: 10,
            sort_by: 'session',
            sort_dir: 'asc',
            search: ''
        };

        const rootEl = document.getElementById('children-table-root');
        const childrenDataUrl = rootEl ? rootEl.getAttribute('data-children-data-url') : '';
        const guardiansSearchUrl = rootEl ? rootEl.getAttribute('data-guardians-search-url') : '';
        const guardiansStoreUrl = rootEl ? rootEl.getAttribute('data-guardians-store-url') : '';
        const hasValidationErrors = rootEl ? rootEl.getAttribute('data-has-validation-errors') === '1' : false;

        async function fetchChildren() {
            const url = new URL(childrenDataUrl || window.location.pathname, window.location.origin);
            url.searchParams.set('page', childrenState.page);
            url.searchParams.set('per_page', childrenState.per_page);
            url.searchParams.set('sort_by', childrenState.sort_by);
            url.searchParams.set('sort_dir', childrenState.sort_dir);
            if (childrenState.search) url.searchParams.set('search', childrenState.search);

            const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!data.success) return;
            renderChildrenTable(data.data);
            renderChildrenMeta(data.meta);
        }

        function msToHMS(ms) {
            const totalSeconds = Math.max(0, Math.floor(ms / 1000));
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            if (hours > 0) {
                return `${hours}h ${minutes}m ${seconds}s`;
            }
            return `${minutes}m ${seconds}s`;
        }

        const activeTimers = new Map();

        function clearAllTimers() {
            activeTimers.forEach((intervalId) => clearInterval(intervalId));
            activeTimers.clear();
        }

        async function stopSession(sessionId, rowEl) {
            if (!sessionId) return;
            try {
                const res = await fetch(`/dashboard-api/sessions/${sessionId}/stop`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (data.success) {
                    // Refresh table data to reflect stopped session
                    fetchChildren();
                } else {
                    alert(data.message || 'Nu s-a putut opri sesiunea');
                }
            } catch (e) {
                alert('Eroare de rețea la oprirea sesiunii');
            }
        }

        function renderChildrenTable(rows) {
            const tbody = document.getElementById('childrenTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';
            clearAllTimers();
            rows.forEach(row => {
                const initials = (row.name?.[0] || '').toUpperCase() + (row.name?.[1] || '').toUpperCase();

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                
                @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin() || Auth::user()->isStaff())
                const actionsHtml = `
                    <div class="flex space-x-2">
                        <a href="/children/${row.id}" class="text-indigo-600 hover:text-indigo-900">Vezi</a>
                        <a href="/children/${row.id}/edit" class="text-yellow-600 hover:text-yellow-900">Editează</a>
                        @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                        <form method="POST" action="/children/${row.id}" class="inline" onsubmit="return confirm('Sigur vrei să ștergi acest copil?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Șterge</button>
                        </form>
                        @endif
                    </div>
                `;
                @else
                const actionsHtml = `
                    <div class="flex space-x-2">
                        <a href="/children/${row.id}" class="text-indigo-600 hover:text-indigo-900">Vezi</a>
                    </div>
                `;
                @endif
                
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-600">${initials}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${row.child_name || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${row.guardian_name || '-'}</div>
                        <div class="text-sm text-gray-500">${row.guardian_phone || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${row.active_session_id ? `
                            <div class="flex items-center space-x-3">
                                <div>
                                    <div class="text-sm text-gray-900">
                                        ${row.is_paused ? '<span class="text-yellow-600">⏸ În Pauză</span>' : 'În desfășurare'} • <span class="font-mono" data-session-timer="${row.active_session_id}"></span>
                                    </div>
                                    <div class="text-xs text-gray-500">Start: ${row.active_started_at ? new Date(row.active_started_at).toLocaleString() : '-'}</div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    ${!row.is_paused ? `<button data-pause-session="${row.active_session_id}" class="px-3 py-1.5 rounded-md bg-yellow-600 text-white text-xs hover:bg-yellow-700">Pauză</button>` : ''}
                                    ${row.is_paused ? `<button data-resume-session="${row.active_session_id}" class="px-3 py-1.5 rounded-md bg-green-600 text-white text-xs hover:bg-green-700">Reia</button>` : ''}
                                    <button data-stop-session="${row.active_session_id}" class="px-3 py-1.5 rounded-md bg-red-600 text-white text-xs hover:bg-red-700">Oprește</button>
                                </div>
                            </div>
                        ` : '<span class="text-gray-400 text-sm">Fără sesiune activă</span>'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        ${actionsHtml}
                    </td>
                `;
                tbody.appendChild(tr);

                // Setup timer if active session
                if (row.active_session_id) {
                    const label = tr.querySelector(`[data-session-timer="${row.active_session_id}"]`);
                    
                    // effective_seconds from backend contains ONLY closed intervals
                    const baseSeconds = row.effective_seconds || 0;
                    const isPaused = row.is_paused || false;
                    const currentIntervalStart = row.current_interval_started_at ? Date.parse(row.current_interval_started_at) : null;
                    
                    const update = () => {
                        let totalSeconds = baseSeconds;
                        // If not paused and we have a current interval, add elapsed time
                        if (!isPaused && currentIntervalStart) {
                            const elapsed = Math.max(0, Math.floor((Date.now() - currentIntervalStart) / 1000));
                            totalSeconds += elapsed;
                        }
                        label.textContent = msToHMS(totalSeconds * 1000);
                    };
                    update();
                    const intervalId = setInterval(update, 1000);
                    activeTimers.set(row.active_session_id, intervalId);

                    const stopBtn = tr.querySelector(`[data-stop-session="${row.active_session_id}"]`);
                    if (stopBtn) {
                        stopBtn.addEventListener('click', () => stopSession(row.active_session_id, tr));
                    }
                    const pauseBtn = tr.querySelector(`[data-pause-session="${row.active_session_id}"]`);
                    if (pauseBtn) {
                        pauseBtn.addEventListener('click', async () => {
                            // Prevent double-click
                            if (pauseBtn.disabled) return;
                            
                            // Disable button immediately and show loader
                            pauseBtn.disabled = true;
                            const originalContent = pauseBtn.innerHTML;
                            pauseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                            pauseBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            
                            try {
                                const res = await fetch(`/dashboard-api/sessions/${row.active_session_id}/pause`, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, credentials: 'same-origin' });
                                const data = await res.json();
                                if (data.success) {
                                    // Refresh entire table to update button states
                                    fetchChildren();
                                } else {
                                    alert(data.message || 'Nu s-a putut pune pe pauză');
                                    pauseBtn.disabled = false;
                                    pauseBtn.innerHTML = originalContent;
                                    pauseBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            } catch (error) {
                                alert('Eroare de rețea la pauză');
                                pauseBtn.disabled = false;
                                pauseBtn.innerHTML = originalContent;
                                pauseBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                        });
                    }
                    const resumeBtn = tr.querySelector(`[data-resume-session="${row.active_session_id}"]`);
                    if (resumeBtn) {
                        resumeBtn.addEventListener('click', async () => {
                            // Prevent double-click
                            if (resumeBtn.disabled) return;
                            
                            // Disable button immediately and show loader
                            resumeBtn.disabled = true;
                            const originalContent = resumeBtn.innerHTML;
                            resumeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                            resumeBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            
                            try {
                                const res = await fetch(`/dashboard-api/sessions/${row.active_session_id}/resume`, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, credentials: 'same-origin' });
                                const data = await res.json();
                                if (data.success) {
                                    // Refresh entire table to update button states and resume timer
                                    fetchChildren();
                                } else {
                                    alert(data.message || 'Nu s-a putut relua');
                                    resumeBtn.disabled = false;
                                    resumeBtn.innerHTML = originalContent;
                                    resumeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            } catch (error) {
                                alert('Eroare de rețea la reluare');
                                resumeBtn.disabled = false;
                                resumeBtn.innerHTML = originalContent;
                                resumeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                        });
                    }
                }
            });
        }

        function renderChildrenMeta(meta) {
            const info = document.getElementById('childrenResultsInfo');
            const pageLabel = document.getElementById('childrenPageLabel');
            const prev = document.getElementById('childrenPrevPage');
            const next = document.getElementById('childrenNextPage');
            const from = (meta.page - 1) * meta.per_page + 1;
            const to = Math.min(meta.page * meta.per_page, meta.total);
            info.textContent = meta.total ? `Afișate ${from}-${to} din ${meta.total}` : 'Nu există rezultate';
            pageLabel.textContent = `Pagina ${meta.page} / ${meta.total_pages || 1}`;
            prev.disabled = meta.page <= 1;
            next.disabled = meta.page >= (meta.total_pages || 1);

            document.querySelectorAll('.sort-ind').forEach(el => {
                const col = el.getAttribute('data-col');
                el.textContent = col === childrenState.sort_by ? (childrenState.sort_dir === 'asc' ? '▲' : '▼') : '';
            });
        }

        // Bind controls
        const perPageEl = document.getElementById('childrenPerPage');
        if (perPageEl) {
            perPageEl.addEventListener('change', (e) => {
                childrenState.per_page = parseInt(e.target.value, 10);
                childrenState.page = 1;
                fetchChildren();
            });
        }

        let childrenSearchDebounce;
        const searchEl = document.getElementById('childrenSearchInput');
        if (searchEl) {
            searchEl.addEventListener('input', (e) => {
                const v = e.target.value.trim();
                if (childrenSearchDebounce) clearTimeout(childrenSearchDebounce);
                childrenSearchDebounce = setTimeout(() => {
                    childrenState.search = v;
                    childrenState.page = 1;
                    fetchChildren();
                }, 300);
            });
        }

        const prevBtn = document.getElementById('childrenPrevPage');
        const nextBtn = document.getElementById('childrenNextPage');
        if (prevBtn) prevBtn.addEventListener('click', () => { if (childrenState.page > 1) { childrenState.page -= 1; fetchChildren(); } });
        if (nextBtn) nextBtn.addEventListener('click', () => { childrenState.page += 1; fetchChildren(); });

        document.querySelectorAll('th[data-sort]').forEach(th => {
            th.addEventListener('click', () => {
                const col = th.getAttribute('data-sort');
                if (childrenState.sort_by === col) {
                    childrenState.sort_dir = childrenState.sort_dir === 'asc' ? 'desc' : 'asc';
                } else {
                    childrenState.sort_by = col;
                    childrenState.sort_dir = 'asc';
                }
                childrenState.page = 1;
                fetchChildren();
            });
        });

        // Initial load of AJAX table
        fetchChildren();
        const modal = document.getElementById('add-child-modal');
        const openBtn = document.getElementById('open-add-child-modal');
        const openBtnEmpty = document.getElementById('open-add-child-modal-empty');
        const closeBtn = document.getElementById('close-add-child-modal');
        const cancelBtn = document.getElementById('cancel-add-child');
        const overlay = document.getElementById('add-child-overlay');
        const notesTextarea = document.getElementById('notes');

        function openModal() { if (modal) modal.classList.remove('hidden'); }
        function closeModal() { if (modal) modal.classList.add('hidden'); }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (openBtnEmpty) openBtnEmpty.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // Close on overlay click
        if (overlay) overlay.addEventListener('click', closeModal);

        // Open via query param ?open=add-child
        const params = new URLSearchParams(window.location.search);
        if (params.get('open') === 'add-child') {
            openModal();
        }

        // Auto-open if validation errors exist
        if (hasValidationErrors) {
            openModal();
        }

        // Character counter for notes
        const maxLength = 1000;
        if (notesTextarea) {
            notesTextarea.addEventListener('input', function() {
                const remaining = maxLength - this.value.length;
                const counter = document.getElementById('modal-notes-counter') || createNotesCounter();
                counter.textContent = `${remaining} caractere rămase`;
                if (remaining < 50) {
                    counter.className = 'mt-1 text-sm text-red-500';
                } else {
                    counter.className = 'mt-1 text-sm text-gray-500';
                }
            });
        }

        function createNotesCounter() {
            const counter = document.createElement('p');
            counter.id = 'modal-notes-counter';
            counter.className = 'mt-1 text-sm text-gray-500';
            notesTextarea.parentNode.appendChild(counter);
            return counter;
        }


        // Inline add guardian via modal (robust to DOM order)
        const addGuardianBtn = document.getElementById('open-add-guardian');
        function openGuardianModal() {
            const modal = document.getElementById('add-guardian-modal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeGuardianModal() {
            const modal = document.getElementById('add-guardian-modal');
            if (modal) modal.classList.add('hidden');
        }
        if (addGuardianBtn) addGuardianBtn.addEventListener('click', openGuardianModal);

        // Event delegation for closing
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.id === 'add-guardian-overlay' || e.target.id === 'close-add-guardian-modal' || e.target.id === 'cancel-add-guardian')) {
                closeGuardianModal();
            }
        });

        // Enhance guardian select with in-select search using Choices.js (remote search)
        const guardianSelect = document.getElementById('guardian_id');
        if (guardianSelect) {
            const choices = new Choices(guardianSelect, {
                searchPlaceholderValue: 'Caută părinte...',
                shouldSort: false,
                removeItemButton: false,
                searchResultLimit: 20,
                searchEnabled: true,
                placeholder: true,
                placeholderValue: 'Selectează părinte'
            });

            let lastQuery = '';
            // Bind to Choices search input
            document.addEventListener('input', async function(e) {
                const searchInput = document.querySelector('.choices__input--cloned');
                if (!searchInput || e.target !== searchInput) return;
                const q = searchInput.value || '';
                if (q === lastQuery) return;
                lastQuery = q;
                try {
                    const url = new URL(guardiansSearchUrl || window.location.pathname, window.location.origin);
                    if (q) url.searchParams.set('q', q);
                    url.searchParams.set('limit', '20');
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success) {
                        const items = data.guardians.map(g => ({ value: String(g.id), label: `${g.name}${g.phone ? ' ('+g.phone+')' : ''}` }));
                        choices.clearChoices();
                        choices.setChoices(items, 'value', 'label', true);
                    }
                } catch (err) {
                    // ignore
                }
            });
        }

        // Handle guardian form submit via delegation
        document.addEventListener('submit', async function(e) {
            if (e.target && e.target.id === 'add-guardian-form') {
                e.preventDefault();
                const form = e.target;
                const firstName = document.getElementById('guardian_first_name').value.trim();
                const lastName = document.getElementById('guardian_last_name').value.trim();
                const phone = document.getElementById('guardian_phone').value.trim();

                if (!firstName || !lastName) {
                    alert('Prenumele și numele părintelui sunt obligatorii.');
                    return;
                }

                const name = `${firstName} ${lastName}`;
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Se salvează...';

                try {
                    const response = await fetch(guardiansStoreUrl || window.location.pathname, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ name, phone })
                    });

                    const data = await response.json();
                    if (data.success && data.guardian) {
                        const select = document.getElementById('guardian_id');
                        const option = document.createElement('option');
                        option.value = data.guardian.id;
                        option.textContent = data.guardian.name + (data.guardian.phone ? ` (${data.guardian.phone})` : '');
                        select.appendChild(option);
                        select.value = data.guardian.id;
                        form.reset();
                        closeGuardianModal();
                    } else {
                        alert(data.message || 'Eroare la adăugarea părintelui');
                    }
                } catch (err) {
                    alert('Eroare de conexiune');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Salvează Părinte';
                }
            }
        });
    })();
</script>
<!-- Add Guardian Modal (Secondary) -->
<div id="add-guardian-modal" class="fixed inset-0 z-[60] hidden" aria-hidden="true">
    <div id="add-guardian-overlay" class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Adaugă Părinte Rapid</h3>
                <button id="close-add-guardian-modal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="add-guardian-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="guardian_first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Prenume <span class="text-red-500">*</span>
                            </label>
                            <input id="guardian_first_name" type="text" maxlength="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ex: Maria" required>
                        </div>
                        <div>
                            <label for="guardian_last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nume <span class="text-red-500">*</span>
                            </label>
                            <input id="guardian_last_name" type="text" maxlength="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ex: Popescu" required>
                        </div>
                    </div>
                    <div>
                        <label for="guardian_phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                        <input id="guardian_phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ex: 0712345678">
                        <p class="mt-1 text-sm text-gray-500">Format: 0712345678 (10 cifre)</p>
                    </div>
                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-gray-200">
                        <button type="button" id="cancel-add-guardian" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Anulează</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Salvează Părinte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

