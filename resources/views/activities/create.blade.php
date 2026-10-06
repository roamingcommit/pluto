<x-app-layout>
    <x-slot name="header">
        <h2>Add Activity</h2>
    </x-slot>

    <p>Trip: {{ $trip->title }}</p>
    <form method="POST" action="{{ route('trips.activities.store', $trip) }}">
        @csrf
        @include('activities._form', ['trip' => $trip, 'activity' => $activity])
        <button type="submit">Save Activity</button>
        <a href="{{ route('trips.show', $trip) }}">Cancel</a>
    </form>
</x-app-layout>
