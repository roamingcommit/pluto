<x-app-layout>
    <x-slot name="header">
        <h2>Edit Activity</h2>
    </x-slot>

    <p>Trip: {{ $trip->title }}</p>
    <form method="POST" action="{{ route('trips.activities.update', [$trip, $activity]) }}">
        @csrf
        @method('PATCH')
        @include('activities._form', ['trip' => $trip, 'activity' => $activity])
        <button type="submit">Save Changes</button>
        <a href="{{ route('trips.activities.show', [$trip, $activity]) }}">Cancel</a>
    </form>
</x-app-layout>
