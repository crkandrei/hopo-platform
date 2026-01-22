@extends('layouts.app')

@section('title', 'Bon Fiscal')
@section('page-title', 'Bon Fiscal')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Bon Fiscal 🧾</h1>
                <p class="text-gray-600 text-lg">Generează bon fiscal pentru ora de joacă</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form id="fiscal-receipt-form">
            @csrf

            <!-- Location Selection -->
            <div class="mb-6">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Locație <span class="text-red-500">*</span>
                </label>
                <select name="location_id" id="location_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Selectați locație --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}@if($location->company) ({{ $location->company->name }})@endif</option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Duration Input -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Hours -->
                <div>
                    <label for="hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Ore <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="hours" 
                           id="hours" 
                           min="0" 
                           max="24" 
                           value="0" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('hours')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Minutes -->
                <div>
                    <label for="minutes" class="block text-sm font-medium text-gray-700 mb-2">
                        Minute <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="minutes" 
                           id="minutes" 
                           min="0" 
                           max="59" 
                           value="0" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('minutes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Calculated Price Display -->
            <div id="price-display" class="mb-6 hidden">
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Durată calculată:</p>
                            <p id="calculated-duration" class="text-lg font-semibold text-gray-900"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Preț calculat:</p>
                            <p id="calculated-price" class="text-2xl font-bold text-indigo-600"></p>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-t border-indigo-200">
                        <p class="text-xs text-gray-500">
                            Tarif pe oră: <span id="hourly-rate" class="font-medium"></span> RON
                            | Ore rotunjite: <span id="rounded-hours" class="font-medium"></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tip plată <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="radio" 
                               name="paymentType" 
                               value="CASH" 
                               checked
                               required
                               class="mr-2 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700">Cash</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" 
                               name="paymentType" 
                               value="CARD" 
                               required
                               class="mr-2 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700">Card</span>
                    </label>
                </div>
                @error('paymentType')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4">
                <button type="button" 
                        onclick="calculatePrice()" 
                        class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-calculator mr-2"></i>
                    Calculează Preț
                </button>
                <button type="submit" 
                        id="submit-btn"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-print mr-2"></i>
                    Emite Bon Fiscal
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Print 1 Leu Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-1">Tipărire Rapidă</h2>
                <p class="text-gray-600 text-sm">Emite rapid un bon fiscal de 1 leu</p>
            </div>
        </div>
        
        <form id="one-leu-form">
            @csrf
            
            <!-- Payment Type for 1 Leu -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tip plată <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="radio" 
                               name="paymentTypeOneLeu" 
                               value="CASH" 
                               checked
                               required
                               class="mr-2 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700">Cash</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" 
                               name="paymentTypeOneLeu" 
                               value="CARD" 
                               required
                               class="mr-2 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700">Card</span>
                    </label>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        id="one-leu-btn"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-receipt mr-2"></i>
                    Tipărire Bon 1 Leu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Calculate price when location or duration changes
    function calculatePrice() {
        const locationId = document.getElementById('location_id').value;
        const hours = parseInt(document.getElementById('hours').value) || 0;
        const minutes = parseInt(document.getElementById('minutes').value) || 0;

        // Validate location is selected
        if (!locationId) {
            alert('Selectați o locație');
            return;
        }

        // Validate inputs
        if (hours < 0 || hours > 24) {
            alert('Orele trebuie să fie între 0 și 24');
            return;
        }
        if (minutes < 0 || minutes > 59) {
            alert('Minutele trebuie să fie între 0 și 59');
            return;
        }

        if (hours === 0 && minutes === 0) {
            alert('Introduceți o durată (ore sau minute)');
            return;
        }

        // Show loading state
        const priceDisplay = document.getElementById('price-display');
        priceDisplay.classList.remove('hidden');
        const loadingHtml = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-indigo-600"></i> <span class="ml-2">Calculare...</span></div>';
        priceDisplay.querySelector('.bg-indigo-50').innerHTML = loadingHtml;

        // Make AJAX request
        fetch('{{ route("fiscal-receipts.calculate-price") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                location_id: locationId,
                hours: hours,
                minutes: minutes
            })
        })
        .then(response => {
            // Check if response is ok (status 200-299)
            if (!response.ok) {
                // Try to parse error response
                return response.json().then(err => {
                    throw new Error(err.message || 'Eroare la calcularea prețului');
                }).catch(() => {
                    throw new Error('Eroare la calcularea prețului (Status: ' + response.status + ')');
                });
            }
            return response.json();
        })
        .then(data => {
            // Check if data has success flag and it's true
            if (data.success === true && data.price !== undefined) {
                // Restore original HTML structure with calculated values
                const priceContainer = priceDisplay.querySelector('.bg-indigo-50');
                priceContainer.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Durată calculată:</p>
                            <p id="calculated-duration" class="text-lg font-semibold text-gray-900">${data.duration}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Preț calculat:</p>
                            <p id="calculated-price" class="text-2xl font-bold text-indigo-600">${data.price.toFixed(2)} RON</p>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-t border-indigo-200">
                        <p class="text-xs text-gray-500">
                            Tarif pe oră: <span id="hourly-rate" class="font-medium">${data.hourlyRate.toFixed(2)}</span> RON
                            | Ore rotunjite: <span id="rounded-hours" class="font-medium">${data.roundedHours.toFixed(2)}</span>
                        </p>
                    </div>
                `;
            } else {
                // If success is false or missing, show error
                const errorMsg = data.message || 'Eroare la calcularea prețului';
                alert(errorMsg);
                document.getElementById('price-display').classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Eroare la calcularea prețului');
            document.getElementById('price-display').classList.add('hidden');
        });
    }

    // Form submission - send to bridge directly from browser
    document.getElementById('fiscal-receipt-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const locationId = document.getElementById('location_id').value;
        const hours = parseInt(document.getElementById('hours').value) || 0;
        const minutes = parseInt(document.getElementById('minutes').value) || 0;
        const paymentType = document.querySelector('input[name="paymentType"]:checked').value;

        // Validate inputs
        if (!locationId) {
            alert('Selectați o locație');
            return false;
        }

        if (hours === 0 && minutes === 0) {
            alert('Introduceți o durată (ore sau minute)');
            return false;
        }

        // Show loading state
        const submitBtn = document.getElementById('submit-btn');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Se emite bonul...';

        try {
            // Step 1: Get calculated data from Laravel backend
            const prepareResponse = await fetch('{{ route("fiscal-receipts.prepare-print") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    location_id: locationId,
                    hours: hours,
                    minutes: minutes,
                    paymentType: paymentType
                })
            });

            if (!prepareResponse.ok) {
                const errorData = await prepareResponse.json();
                throw new Error(errorData.message || 'Eroare la pregătirea datelor');
            }

            const prepareData = await prepareResponse.json();
            
            if (!prepareData.success || !prepareData.data) {
                throw new Error('Date invalide de la server');
            }

            // Step 2: Send directly to local bridge from browser
            const bridgeUrl = 'http://localhost:9000';
            const bridgeResponse = await fetch(`${bridgeUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(prepareData.data)
            });

            if (!bridgeResponse.ok) {
                const errorText = await bridgeResponse.text();
                let errorData;
                try {
                    errorData = JSON.parse(errorText);
                } catch {
                    throw new Error(`Eroare HTTP ${bridgeResponse.status}: ${errorText.substring(0, 100)}`);
                }
                throw new Error(errorData.message || errorData.details || 'Eroare de la bridge-ul fiscal');
            }

            const bridgeData = await bridgeResponse.json();

            // Step 3: Send result to Laravel backend for session message handling
            let resultPayload;
            if (bridgeData.status === 'success') {
                resultPayload = {
                    status: 'success',
                    message: 'Bon fiscal emis cu succes!',
                    file: bridgeData.file || null,
                    price: prepareData.data.price,
                    duration: prepareData.data.duration,
                    paymentType: prepareData.data.paymentType,
                };
            } else {
                resultPayload = {
                    status: 'error',
                    message: bridgeData.message || 'Eroare necunoscută',
                    details: bridgeData.details || null,
                };
            }

            // Send result to Laravel backend using form submit to follow redirects properly
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("fiscal-receipts.handle-result") }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add all payload fields
            for (const [key, value] of Object.entries(resultPayload)) {
                if (value !== null && value !== undefined) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
            }
            
            document.body.appendChild(form);
            form.submit();
        } catch (error) {
            console.error('Error:', error);
            
            // Send error to Laravel backend for proper display using form submit
            try {
                const errorPayload = {
                    status: 'error',
                    message: error.message.includes('Failed to fetch') || error.message.includes('NetworkError')
                        ? 'Nu s-a putut conecta la bridge-ul fiscal local (localhost:9000). Verifică că serviciul Node.js rulează pe calculatorul tău.'
                        : error.message,
                    details: null,
                };

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("fiscal-receipts.handle-result") }}';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // Add all payload fields
                for (const [key, value] of Object.entries(errorPayload)) {
                    if (value !== null && value !== undefined) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        form.appendChild(input);
                    }
                }
                
                document.body.appendChild(form);
                form.submit();
            } catch (fallbackError) {
                // Last resort fallback
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                alert('Eroare: ' + error.message);
            }
        }
    });

    // Handle 1 leu receipt form submission
    document.getElementById('one-leu-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const paymentType = document.querySelector('input[name="paymentTypeOneLeu"]:checked').value;

        // Show loading state
        const submitBtn = document.getElementById('one-leu-btn');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Se emite bonul...';

        try {
            // Step 1: Get prepared data from Laravel backend
            const prepareResponse = await fetch('{{ route("fiscal-receipts.prepare-print-one-leu") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    paymentType: paymentType
                })
            });

            if (!prepareResponse.ok) {
                const errorData = await prepareResponse.json();
                throw new Error(errorData.message || 'Eroare la pregătirea datelor');
            }

            const prepareData = await prepareResponse.json();
            
            if (!prepareData.success || !prepareData.data) {
                throw new Error('Date invalide de la server');
            }

            // Step 2: Send directly to local bridge from browser
            const bridgeUrl = 'http://localhost:9000';
            const bridgeResponse = await fetch(`${bridgeUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(prepareData.data)
            });

            if (!bridgeResponse.ok) {
                const errorText = await bridgeResponse.text();
                let errorData;
                try {
                    errorData = JSON.parse(errorText);
                } catch {
                    throw new Error(`Eroare HTTP ${bridgeResponse.status}: ${errorText.substring(0, 100)}`);
                }
                throw new Error(errorData.message || errorData.details || 'Eroare de la bridge-ul fiscal');
            }

            const bridgeData = await bridgeResponse.json();

            // Step 3: Send result to Laravel backend for session message handling
            let resultPayload;
            if (bridgeData.status === 'success') {
                resultPayload = {
                    status: 'success',
                    message: 'Bon fiscal de 1 leu emis cu succes!',
                    file: bridgeData.file || null,
                    price: prepareData.data.price,
                    duration: prepareData.data.duration,
                    paymentType: prepareData.data.paymentType,
                };
            } else {
                resultPayload = {
                    status: 'error',
                    message: bridgeData.message || 'Eroare necunoscută',
                    details: bridgeData.details || null,
                };
            }

            // Send result to Laravel backend using form submit to follow redirects properly
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("fiscal-receipts.handle-result") }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add all payload fields
            for (const [key, value] of Object.entries(resultPayload)) {
                if (value !== null && value !== undefined) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
            }
            
            document.body.appendChild(form);
            form.submit();
        } catch (error) {
            console.error('Error:', error);
            
            // Send error to Laravel backend for proper display using form submit
            try {
                const errorPayload = {
                    status: 'error',
                    message: error.message.includes('Failed to fetch') || error.message.includes('NetworkError')
                        ? 'Nu s-a putut conecta la bridge-ul fiscal local (localhost:9000). Verifică că serviciul Node.js rulează pe calculatorul tău.'
                        : error.message,
                    details: null,
                };

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("fiscal-receipts.handle-result") }}';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // Add all payload fields
                for (const [key, value] of Object.entries(errorPayload)) {
                    if (value !== null && value !== undefined) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        form.appendChild(input);
                    }
                }
                
                document.body.appendChild(form);
                form.submit();
            } catch (fallbackError) {
                // Last resort fallback
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                alert('Eroare: ' + error.message);
            }
        }
    });
</script>
@endsection
