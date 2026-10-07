<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">{{ $activity->title }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-3xl gap-4 rounded-lg border border-gray-200 bg-white p-4 text-gray-900 break-words sm:p-6">
            @if (session('status'))
                <p role="status" class="rounded-md border border-green-200 bg-green-50 p-3 text-green-800">{{ session('status') }}</p>
            @endif

            <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded" href="{{ route('trips.show', $trip) }}">Back to Trip</a>
            <p>Trip: {{ $trip->title }}</p>
            <p>Date and time: {{ $activity->starts_at->format('d M Y, H:i') }} (destination local time)</p>
            <p>Location: {{ $activity->location ?? 'No location added' }}</p>
            <h3 class="font-semibold text-gray-900">Notes</h3>
            <p style="white-space: pre-wrap">{{ $activity->notes ?? 'No notes added' }}</p>

            <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded" href="{{ route('trips.activities.edit', [$trip, $activity]) }}">Edit Activity</a>
            <form class="grid gap-4" method="POST" action="{{ route('trips.activities.destroy', [$trip, $activity]) }}"
                onsubmit="return confirm('Delete this activity permanently? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <x-breeze.danger-button class="justify-self-start">Delete Activity</x-breeze.danger-button>
            </form>
        </div>
    </div>
</x-app-layout>
