<x-app-layout>
    <x-slot name="header">
        <h2>{{ $activity->title }}</h2>
    </x-slot>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <a href="{{ route('trips.show', $trip) }}">Back to Trip</a>
    <p>Trip: {{ $trip->title }}</p>
    <p>Date and time: {{ $activity->starts_at->format('d M Y, H:i') }} (destination local time)</p>
    <p>Location: {{ $activity->location ?? 'No location added' }}</p>
    <h3>Notes</h3>
    <p style="white-space: pre-wrap">{{ $activity->notes ?? 'No notes added' }}</p>

    <a href="{{ route('trips.activities.edit', [$trip, $activity]) }}">Edit Activity</a>
    <form method="POST" action="{{ route('trips.activities.destroy', [$trip, $activity]) }}"
          onsubmit="return confirm('Delete this activity permanently? This cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit">Delete Activity</button>
    </form>
</x-app-layout>
