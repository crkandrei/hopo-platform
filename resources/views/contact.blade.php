@extends('layouts.landing')

@section('title', 'Contact HOPO – Solicită Demo Gratuit pentru Locul de Joacă')
@section('meta_description', 'Contactează echipa HOPO pentru un demo gratuit al software-ului de gestiune pentru locuri de joacă. Răspundem în maxim 24 de ore.')
@section('canonical', 'https://hopo.ro/contact')

@section('content')
    <section id="contact" class="pt-28 pb-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12">
                <div data-animate>
                    <h1 class="text-3xl font-bold mb-4">Solicită un demo gratuit</h1>
                    <p class="text-gray-600 mb-8">
                        Completează formularul și te contactăm în maxim 24 de ore pentru a programa un demo.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-hopo-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-600">contact@hopo.ro</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-hopo-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <a href="tel:0752620694" class="text-gray-600 hover:text-hopo-purple transition-colors">0752 620 694</a>
                        </div>
                    </div>
                </div>
                <div data-animate data-delay="150">
                    <form id="contact-form" class="space-y-4" method="POST" action="/contact">
                        @csrf

                        <div id="contact-success" class="hidden p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4">
                            <p class="font-medium">Mulțumim pentru mesaj!</p>
                            <p class="text-sm">Te vom contacta în cel mai scurt timp.</p>
                        </div>

                        <div id="contact-error" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 mb-4">
                            <p class="font-medium">Eroare</p>
                            <p id="contact-error-message" class="text-sm"></p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume complet</label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="Ion Popescu" value="{{ old('name') }}">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="ion@locjoacă.ro" value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                            <input type="tel" id="phone" name="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="0752 620 694" value="{{ old('phone') }}">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="playground_name" class="block text-sm font-medium text-gray-700 mb-1">Numele locului de joacă</label>
                            <input type="text" id="playground_name" name="playground_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="FunPark" value="{{ old('playground_name') }}">
                            @error('playground_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" id="contact-submit" class="w-full bg-hopo-purple hover:bg-hopo-purple-dark text-white py-3 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="contact-submit-text">Trimite cererea</span>
                            <span id="contact-submit-loading" class="hidden">Se trimite...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.getElementById('contact-form');
        const successMessage = document.getElementById('contact-success');
        const errorMessage = document.getElementById('contact-error');
        const errorMessageText = document.getElementById('contact-error-message');
        const submitButton = document.getElementById('contact-submit');
        const submitText = document.getElementById('contact-submit-text');
        const submitLoading = document.getElementById('contact-submit-loading');

        if (contactForm) {
            contactForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                successMessage.classList.add('hidden');
                errorMessage.classList.add('hidden');

                submitButton.disabled = true;
                submitText.classList.add('hidden');
                submitLoading.classList.remove('hidden');

                const formData = new FormData(contactForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch(contactForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        successMessage.classList.remove('hidden');
                        contactForm.reset();
                    } else {
                        errorMessage.classList.remove('hidden');
                        errorMessageText.textContent = data.message || 'A apărut o eroare. Te rugăm să încerci din nou.';
                    }
                } catch (error) {
                    errorMessage.classList.remove('hidden');
                    errorMessageText.textContent = 'A apărut o eroare de rețea. Te rugăm să încerci din nou.';
                } finally {
                    submitButton.disabled = false;
                    submitText.classList.remove('hidden');
                    submitLoading.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush
