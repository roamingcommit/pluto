<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">{{ $trip->title }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-3xl gap-4 rounded-lg border border-gray-200 bg-white p-4 text-gray-900 break-words sm:p-6">
            <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded" href="{{ route('trips.index') }}">Back to My Trips</a>
            @if (session('status'))
                <p role="status" class="rounded-md border border-green-200 bg-green-50 p-3 text-green-800">{{ session('status') }}</p>
            @endif

            <p>Start date: {{ $trip->start_date }}</p>
            <p>End date: {{ $trip->end_date }}</p>
            <p>Hotel: {{ $trip->hotel ?? 'No hotel added' }}</p>
            <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded" href="{{ route('trips.edit', $trip) }}">Edit Trip</a>

            <form class="grid gap-4" method="POST" action="{{ route('trips.destroy', $trip) }}"
                onsubmit="return confirm('Delete this trip permanently? Its activities will also be deleted. This cannot be undone.');">
                @csrf
                @method('DELETE')

                <x-breeze.danger-button class="justify-self-start">Delete Trip</x-breeze.danger-button>
            </form>

            <section class="grid gap-3 border-t border-gray-200 pt-4" aria-labelledby="activities-heading">
                <h2 class="text-lg font-semibold text-gray-900" id="activities-heading">Activities</h2>
                <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded" href="{{ route('trips.activities.create', $trip) }}">Add Activity</a>
                <ul class="grid gap-3">
                    @forelse ($activities as $activity)
                        <li>
                            <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded" href="{{ route('trips.activities.show', [$trip, $activity]) }}">{{ $activity->title }}</a>
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
        </div>
    </div>
</x-app-layout>
