@extends('layouts.booking')

@section('title', 'Rezervare înregistrată - ' . $location->name)
@section('header-subtitle', 'Rezervare zi de naștere')
@section('header-title', $location->name)

@section('content')
<div class="space-y-5">

    {{-- Success banner --}}
    <div class="bg-green-500 rounded-xl p-7 text-white text-center shadow-sm">
        <div class="flex items-center justify-center w-14 h-14 bg-white/20 rounded-full mx-auto mb-3">
            <i class="fas fa-check text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold mb-1">Rezervarea a fost înregistrată!</h2>
        <p class="text-green-100 text-sm">
            Vă mulțumim, <strong class="text-white">{{ $reservation->guardian_name }}</strong>!
            Echipa noastră vă va contacta în curând pentru confirmare.
        </p>
    </div>

    {{-- Reservation details --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-receipt text-hopo-purple"></i>
                Detalii rezervare
            </h3>
        </div>
        <div class="divide-y divide-gray-100">
            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-calendar-day text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Data</p>
                    <p class="font-medium text-gray-900 text-sm">{{ $reservation->reservation_date->format('d.m.Y') }}</p>
                </div>
            </div>

            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-clock text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Interval orar</p>
                    <p class="font-medium text-gray-900 text-sm">
                        @if($reservation->timeSlot)
                            {{ \Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i') }}
                        @elseif($reservation->reservation_time && $reservation->birthdayPackage)
                            @php
                                $start = \Carbon\Carbon::parse($reservation->reservation_time);
                                $end = $start->copy()->addMinutes($reservation->birthdayPackage->duration_minutes);
                            @endphp
                            {{ $start->format('H:i') }} – {{ $end->format('H:i') }}
                        @elseif($reservation->reservation_time)
                            {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }}
                        @else
                            —
                        @endif
                    </p>
                </div>
            </div>

            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-door-open text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Sală</p>
                    <p class="font-medium text-gray-900 text-sm">{{ $reservation->birthdayHall->name }}</p>
                </div>
            </div>

            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-box-open text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Pachet</p>
                    <p class="font-medium text-gray-900 text-sm">{{ $reservation->birthdayPackage->name }}</p>
                </div>
            </div>

            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-birthday-cake text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Aniversarul</p>
                    <p class="font-medium text-gray-900 text-sm">
                        {{ $reservation->child_name }}
                        @if($reservation->child_age)
                            <span class="font-normal text-gray-500">({{ $reservation->child_age }} ani)</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-users text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Număr copii</p>
                    <p class="font-medium text-gray-900 text-sm">{{ $reservation->number_of_children }} copii</p>
                </div>
            </div>

            @if($reservation->number_of_adults !== null)
            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-user-friends text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Număr adulți</p>
                    <p class="font-medium text-gray-900 text-sm">{{ $reservation->number_of_adults }} adulți</p>
                </div>
            </div>
            @endif

            <div class="px-6 py-3 flex items-center gap-4">
                <i class="fas fa-user text-hopo-purple w-4 text-center flex-shrink-0"></i>
                <div>
                    <p class="text-xs text-gray-400">Contact</p>
                    <p class="font-medium text-gray-900 text-sm">{{ $reservation->guardian_name }}</p>
                    <p class="text-sm text-gray-500">{{ $reservation->guardian_phone }}</p>
                    @if($reservation->guardian_email)
                        <p class="text-sm text-gray-500">{{ $reservation->guardian_email }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Location contact --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
        <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2 text-sm">
            <i class="fas fa-map-marker-alt text-hopo-purple"></i>
            Date de contact — {{ $location->name }}
        </h3>
        <div class="space-y-2 text-sm text-gray-700">
            @if($location->address)
                <div class="flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-hopo-purple w-4 text-center flex-shrink-0"></i>
                    {{ $location->address }}
                </div>
            @endif
            @if($location->phone)
                <div class="flex items-center gap-2">
                    <i class="fas fa-phone text-hopo-purple w-4 text-center flex-shrink-0"></i>
                    <a href="tel:{{ $location->phone }}" class="hover:text-hopo-purple font-medium">{{ $location->phone }}</a>
                </div>
            @endif
            @if($location->email)
                <div class="flex items-center gap-2">
                    <i class="fas fa-envelope text-hopo-purple w-4 text-center flex-shrink-0"></i>
                    <a href="mailto:{{ $location->email }}" class="hover:text-hopo-purple">{{ $location->email }}</a>
                </div>
            @endif
        </div>
        <p class="mt-4 text-sm text-indigo-700 bg-white/70 rounded-lg px-4 py-2.5">
            <i class="fas fa-info-circle mr-1.5 text-hopo-purple"></i>
            Veți fi contactat(ă) la numărul de telefon furnizat pentru confirmare și detalii suplimentare.
        </p>
    </div>

    <div class="text-center">
        <a href="{{ route('booking.show', $location) }}"
            class="inline-flex items-center gap-2 text-sm text-hopo-purple hover:text-hopo-purple-dark font-medium">
            <i class="fas fa-plus-circle"></i>
            Fă o altă rezervare
        </a>
    </div>

</div>
@endsection
