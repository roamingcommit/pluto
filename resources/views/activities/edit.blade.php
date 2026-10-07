<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Edit Activity</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-3xl gap-4 rounded-lg border border-gray-200 bg-white p-4 text-gray-900 wrap-break-word sm:p-6">
            <p>Trip: {{ $trip->title }}</p>
            <form class="grid gap-4" method="POST" action="{{ route('trips.activities.update', [$trip, $activity]) }}">
                @csrf
                @method('PATCH')
                @include('activities._form', ['trip' => $trip, 'activity' => $activity])
                <x-breeze.primary-button class="justify-self-start">Save Changes</x-breeze.primary-button>
                <a class="text-indigo-700 underline underline-offset-2 hover:text-indigo-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-sm" href="{{ route('trips.activities.show', [$trip, $activity]) }}">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>
