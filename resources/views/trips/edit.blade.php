<x-app-layout>
    <x-slot name="header">
        <h2>Edit Trip</h2>
    </x-slot>

    <p>You are editing: {{ $trip->title }}</p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('trips.update', $trip) }}">
        @csrf
        @method('PATCH')

        <div>
            <label for="title">Trip title</label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title', $trip->title) }}"
                required
            >
        </div>

        <div>
            <label for="hotel">Hotel (optional)</label>
            <input
                type="text"
                id="hotel"
                name="hotel"
                value="{{ old('hotel', $trip->hotel) }}"
            >
        </div>

        <div>
            <label for="start_date">Start date</label>
            <input type="date" id="start_date" name="start_date"
                   value="{{ old('start_date', substr($trip->start_date, 0, 10)) }}" required>
        </div>

        <div>
            <label for="end_date">End date</label>
            <input type="date" id="end_date" name="end_date"
                   value="{{ old('end_date', substr($trip->end_date, 0, 10)) }}" required>
        </div>

        <button type="submit">Save Changes</button>
        <a href="{{ route('trips.show', $trip) }}">Cancel</a>
    </form>
</x-app-layout>
