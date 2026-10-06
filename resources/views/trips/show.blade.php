<x-app-layout>
    <a href="{{ route('trips.index') }}">Back to My Trips</a>
    <x-slot name="header">
        <h2>{{ $trip->title }}</h2>
    </x-slot>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <p>Start date: {{ $trip->start_date }}</p>
    <p>End date: {{ $trip->end_date }}</p>
    <p>Hotel: {{ $trip->hotel ?? 'No hotel added' }}</p>
    <a href="{{ route('trips.edit', $trip) }}">Edit Trip</a>

    <form method="POST" action="{{ route('trips.destroy', $trip) }}"
          onsubmit="return confirm('Delete this trip permanently? Its activities will also be deleted. This cannot be undone.');">
        @csrf
        @method('DELETE')

        <button type="submit">Delete Trip</button>
    </form>

    <section aria-labelledby="activities-heading">
        <h2 id="activities-heading">Activities</h2>
        <a href="{{ route('trips.activities.create', $trip) }}">Add Activity</a>
        <ul>
            @forelse ($activities as $activity)
                <li>
                    <a href="{{ route('trips.activities.show', [$trip, $activity]) }}">{{ $activity->title }}</a>
                    — {{ $activity->starts_at->format('d M Y, H:i') }}
                    @if ($activity->location)
                        — {{ $activity->location }}
                    @endif
                </li>
            @empty
                <li>No activities yet. Add your first activity for this trip.</li>
            @endforelse
        </ul>
    </section>
</x-app-layout>
