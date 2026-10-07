<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Create Trip</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-3xl gap-4 rounded-lg border border-gray-200 bg-white p-4 text-gray-900 wrap-break-word sm:p-6">
            <p>Add the details of your new trip.</p>

            @if ($errors->any())
                <ul role="alert" class="grid gap-1 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form class="grid gap-4" method="POST" action="{{ url('/trips') }}">
                @csrf

                <div class="grid min-w-0 gap-2">
                    <label class="block text-sm font-medium text-gray-700" for="title">Trip title</label>
                    <input class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" type="text" id="title" name="title" value="{{ old('title') }}" required>
                </div>

                <div class="grid min-w-0 gap-2">
                    <label class="block text-sm font-medium text-gray-700" for="start_date">Start date</label>
                    <input class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                </div>

                <div class="grid min-w-0 gap-2">
                    <label class="block text-sm font-medium text-gray-700" for="end_date">End date</label>
                    <input class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                </div>

                <div class="grid min-w-0 gap-2">
                    <label class="block text-sm font-medium text-gray-700" for="hotel">Hotel (optional)</label>
                    <input class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" type="text" id="hotel" name="hotel" value="{{ old('hotel') }}">
                </div>

                <x-breeze.primary-button class="justify-self-start">Save Trip</x-breeze.primary-button>
                <a class="text-indigo-700 underline underline-offset-2" href="{{ route('trips.index') }}">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>
