<x-app-layout>
    <a href="{{ route('trips.index') }}">Back to My Trips</a>
    <x-slot name="header">
        <h2>{{ $trip->title }}</h2>
    </x-slot>

    <p>Start date: {{ $trip->start_date }}</p>
    <p>End date: {{ $trip->end_date }}</p>
    <p>Hotel: {{ $trip->hotel ?? 'No hotel added' }}</p>
</x-app-layout>
