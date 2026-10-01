<x-app-layout>
    <x-slot name="header">
        <h2>My Trips</h2>
    </x-slot>

    <ul>
        @forelse ($trips as $trip)
            <li>
                <a href="{{ route('trips.show', $trip) }}">
                    {{ $trip->title }}
                </a>
            </li>
        @empty
            <li>You haven't added any trips yet.</li>
        @endforelse
    </ul>
</x-app-layout>
