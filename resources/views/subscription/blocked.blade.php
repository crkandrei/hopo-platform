<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acces suspendat — Hopo</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
        <div class="text-5xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Acces suspendat</h1>

        <p class="text-gray-600 mb-1">
            Locație: <span class="font-semibold text-gray-800">{{ $location->name ?? 'Locație necunoscută' }}</span>
        </p>

        @if($subscription && $subscription->expires_at)
            <p class="text-gray-600 mb-6">
                Abonament expirat pe: <span class="font-semibold text-red-600">{{ $subscription->expires_at->format('d M Y') }}</span>
            </p>
        @else
            <p class="text-gray-600 mb-6">Niciun abonament activ găsit.</p>
        @endif

        @if(auth()->user()->isCompanyAdmin())
            <div class="mb-6">
                <a href="{{ route('checkout.plans') }}"
                   class="w-full inline-block px-4 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                    Reînnoiește abonamentul
                </a>
            </div>
        @endif

        <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm text-gray-600">
            <p class="font-semibold text-gray-800 mb-1">Contact pentru reînnoire abonament:</p>
            <p>contact@hopo.ro &middot; 0700 000 000</p>
        </div>

        @if(auth()->user()->isCompanyAdmin() && !empty($eligibleLocations) && count($eligibleLocations) > 0)
            <div class="border-t pt-6 mb-6">
                <p class="text-sm font-semibold text-gray-700 mb-3">Schimbă locația</p>
                <div class="space-y-2">
                    @foreach($eligibleLocations as $eligibleLocation)
                        <button type="button"
                            onclick="switchLocation({{ $eligibleLocation->id }})"
                            class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                            {{ $eligibleLocation->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <script>
            function switchLocation(locationId) {
                fetch('{{ route('location-context.set') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ location_id: locationId })
                }).then(function(res) {
                    if (res.ok) {
                        window.location.href = '/dashboard';
                    }
                });
            }
            </script>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors">
                Deconectare
            </button>
        </form>
    </div>
</body>
</html>
