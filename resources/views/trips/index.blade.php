<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">My Trips</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-3xl gap-4 rounded-lg border border-gray-200 bg-white p-4 text-gray-900 wrap-break-word sm:p-6">
            <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-sm" href="{{ route('trips.create') }}">Create Trip</a>
            <ul class="grid gap-3">
                @forelse ($trips as $trip)
                    <li>
                        <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-sm" href="{{ route('trips.show', $trip) }}">
                            {{ $trip->title }}
                        </a>
                    </li>
                @empty
                    <li>You haven't added any trips yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
