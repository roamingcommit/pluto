<x-app-layout>
    <x-slot name="header">
        <h2>Create Trip</h2>
    </x-slot>

    <p>Add the details of your new trip.</p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ url('/trips') }}">
        @csrf

        <div>
            <label for="title">Trip title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required>
        </div>

        <div>
            <label for="start_date">Start date</label>
            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
        </div>

        <div>
            <label for="end_date">End date</label>
            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
        </div>

        <div>
            <label for="hotel">Hotel (optional)</label>
            <input type="text" id="hotel" name="hotel" value="{{ old('hotel') }}">
        </div>

        <button type="submit">Save Trip</button>
    </form>
</x-app-layout>
