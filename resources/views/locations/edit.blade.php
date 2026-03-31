@extends('layouts.app')

@section('title', 'Editează Locație')
@section('page-title', 'Editează Locație')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Editează Locație 📍</h1>
                <p class="text-gray-600 text-lg">Actualizați informațiile locației</p>
            </div>
            <a href="{{ route('locations.index') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('locations.update', $location) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Company Selection (only for Super Admin) -->
                @if(Auth::user()->isSuperAdmin() && $companies)
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Companie <span class="text-red-500">*</span>
                    </label>
                    <select name="company_id" 
                            id="company_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Selectați companie --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $location->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nume Locație <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name', $location->name) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Adresă
                    </label>
                    <textarea name="address" 
                              id="address"
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $location->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Telefon
                    </label>
                    <input type="text" 
                           name="phone" 
                           id="phone"
                           value="{{ old('phone', $location->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email"
                           value="{{ old('email', $location->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Per Hour -->
                <div>
                    <label for="price_per_hour" class="block text-sm font-medium text-gray-700 mb-2">
                        Tarif pe Oră (RON) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="price_per_hour" 
                           id="price_per_hour"
                           step="0.01"
                           min="0"
                           value="{{ old('price_per_hour', $location->price_per_hour) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('price_per_hour')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $location->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Locație activă</span>
                    </label>
                </div>

                <!-- Bracelet Required -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="bracelet_required"
                               value="1"
                               {{ old('bracelet_required', $location->bracelet_required ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Sesiunile necesită brățară</span>
                    </label>
                </div>

                <!-- Fiscal Enabled -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="fiscal_enabled"
                               value="1"
                               {{ old('fiscal_enabled', $location->fiscal_enabled ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Locația folosește fiscalizare (bon fiscal)</span>
                    </label>
                </div>

                <!-- Pre Check-in Enabled -->
                <div>
                    <label class="flex items-start gap-2">
                        <input type="checkbox"
                               name="pre_checkin_enabled"
                               value="1"
                               {{ old('pre_checkin_enabled', $location->pre_checkin_enabled ?? false) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">
                            Activează Pre Check-in QR
                            <span class="block text-xs text-gray-400 mt-0.5">Părinții pot scana un cod QR înainte de intrare și completa datele pe telefon.</span>
                        </span>
                    </label>
                </div>

                <!-- Birthday Concurrent Reservations (Super Admin only) -->
                @if(Auth::user()->isSuperAdmin())
                <div>
                    <label class="flex items-start gap-2">
                        <input type="checkbox"
                               name="birthday_concurrent_reservations"
                               value="1"
                               {{ old('birthday_concurrent_reservations', $location->birthday_concurrent_reservations ?? false) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">
                            Permite rezervări concomitente pentru zile de naștere
                            <span class="block text-xs text-gray-400 mt-0.5">Părinții pot rezerva orice oră fără a vedea sau fi blocați de alte rezervări existente în aceeași zi.</span>
                        </span>
                    </label>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('locations.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Anulează
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>
                        Actualizează Locație
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── QR Pre Check-in ────────────────────────────────────────────────── --}}
    @if($location->pre_checkin_enabled)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-qrcode mr-2 text-indigo-500"></i>QR Pre Check-in
        </h2>
        <p class="text-sm text-gray-600 mb-6">
            Printați sau afișați acest cod QR la intrare. Părinții îl scanează pentru a completa datele pe telefon înainte de a ajunge la recepție.
        </p>

        <div class="flex flex-col items-center gap-4">
            <div id="pre-checkin-qr" class="p-4 bg-white border-2 border-gray-200 rounded-xl inline-block"></div>
            <p class="text-xs text-gray-500 font-mono break-all text-center">
                {{ route('pre-checkin.index', $location) }}
            </p>
            <button type="button"
                    onclick="window.print()"
                    class="px-5 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                <i class="fas fa-print mr-2"></i>Printează QR
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('pre-checkin-qr'), {
            text: @json(route('pre-checkin.index', $location)),
            width: 220,
            height: 220,
            correctLevel: QRCode.CorrectLevel.H,
        });
    </script>
    @endif

    {{-- ── Configurare Bridge ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6"
         x-data="{
             showKey: false,
             apiKey: '{{ $bridge?->api_key ?? '' }}',
             confirmRegenerate: false,
             async generateKey() {
                 if (this.apiKey && !this.confirmRegenerate) {
                     this.confirmRegenerate = true;
                     return;
                 }
                 this.confirmRegenerate = false;
                 const res = await fetch('{{ route('locations.bridge.generate-key', $location) }}', {
                     method: 'POST',
                     headers: {
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                         'Accept': 'application/json',
                     },
                 });
                 if (res.ok) {
                     const data = await res.json();
                     this.apiKey = data.api_key;
                     this.showKey = true;
                 }
             }
         }">

        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-plug mr-2 text-indigo-500"></i>Configurare Bridge
        </h2>

        {{-- API Key --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
            <div class="flex items-center gap-3">
                <input type="text"
                       :value="showKey ? apiKey : (apiKey ? '••••••••••••••••' : 'Negenerată')"
                       readonly
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm">
                <button type="button"
                        @click="showKey = !showKey"
                        x-show="apiKey"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100">
                    <span x-text="showKey ? 'Ascunde' : 'Arată'"></span>
                </button>
            </div>

            {{-- Confirm regenerate warning --}}
            <div x-show="confirmRegenerate" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                <strong>Atenție:</strong> Bridge-ul existent va trebui reconfigurat manual cu noul key.
                <button type="button" @click="generateKey()"
                        class="ml-3 px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-xs">
                    Confirmă regenerarea
                </button>
                <button type="button" @click="confirmRegenerate = false"
                        class="ml-2 px-3 py-1 border border-yellow-400 rounded text-xs hover:bg-yellow-100">
                    Anulează
                </button>
            </div>

            <button type="button"
                    @click="generateKey()"
                    x-show="!confirmRegenerate"
                    class="mt-3 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                <i class="fas fa-key mr-2"></i>
                <span x-text="apiKey ? 'Regenerează API Key' : 'Generează API Key'"></span>
            </button>
        </div>

        {{-- Bridge Status (only if bridge record exists) --}}
        @if($bridge)
        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 mb-1">Status</div>
                @if($bridge->status === 'online')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span> Online
                    </span>
                @elseif($bridge->status === 'offline')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span> Offline
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        <span class="w-2 h-2 rounded-full bg-gray-400 mr-1"></span> Neconfigurat
                    </span>
                @endif
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 mb-1">Ultima activitate</div>
                <div class="text-sm font-medium">{{ $bridge->last_seen_at?->format('d.m.Y H:i:s') ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 mb-1">Versiune / Mod</div>
                <div class="text-sm font-medium">{{ $bridge->version ?? '—' }} / {{ $bridge->mode ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 mb-1">Bonuri / Z / Erori</div>
                <div class="text-sm font-medium">{{ $bridge->print_count }} / {{ $bridge->z_report_count }} / {{ $bridge->error_count }}</div>
            </div>
        </div>

        {{-- Quick Commands --}}
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Comenzi rapide</h3>
            <div class="flex gap-3 flex-wrap">
                <form method="POST" action="{{ route('locations.bridge.commands', $location) }}">
                    @csrf
                    <input type="hidden" name="command" value="restart">
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700"
                            onclick="return confirm('Trimiți comandă restart bridge?')">
                        <i class="fas fa-redo mr-2"></i>Restart bridge
                    </button>
                </form>
                <form method="POST" action="{{ route('locations.bridge.commands', $location) }}">
                    @csrf
                    <input type="hidden" name="command" value="set_config">
                    <input type="hidden" name="payload[BRIDGE_MODE]" value="test">
                    <button type="submit" class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600">
                        <i class="fas fa-flask mr-2"></i>Mod test
                    </button>
                </form>
                <form method="POST" action="{{ route('locations.bridge.commands', $location) }}">
                    @csrf
                    <input type="hidden" name="command" value="set_config">
                    <input type="hidden" name="payload[BRIDGE_MODE]" value="live">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                        <i class="fas fa-check-circle mr-2"></i>Mod live
                    </button>
                </form>
                <form method="POST" action="{{ route('locations.bridge.commands', $location) }}"
                      x-data="{ version: '' }"
                      @submit.prevent="
                          if (!version.match(/^\d+\.\d+\.\d+$/)) { alert('Versiune invalidă. Format: X.Y.Z (ex: 1.0.2)'); return; }
                          if (!confirm('Trimiți comandă update la v' + version + '?')) return;
                          $el.submit();
                      ">
                    @csrf
                    <input type="hidden" name="command" value="update">
                    <input type="hidden" name="payload[version]" :value="version">
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="version" placeholder="1.0.2"
                               class="w-24 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-download mr-2"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Recent Logs --}}
        @if($recentLogs->isNotEmpty())
        <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Loguri recente (ultimele 50)</h3>
            <div class="overflow-auto max-h-64 border border-gray-200 rounded-lg">
                <table class="min-w-full text-xs font-mono">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-500">Timestamp</th>
                            <th class="px-3 py-2 text-left text-gray-500">Level</th>
                            <th class="px-3 py-2 text-left text-gray-500">Mesaj</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-1.5 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d.m H:i:s') }}</td>
                            <td class="px-3 py-1.5">
                                @if($log->level === 'error')
                                    <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700">error</span>
                                @elseif($log->level === 'warn')
                                    <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700">warn</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">info</span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5 text-gray-800">{{ $log->message }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
