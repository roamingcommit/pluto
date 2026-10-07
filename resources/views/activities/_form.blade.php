@if ($errors->any())
    <ul role="alert" class="grid gap-1 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div class="grid min-w-0 gap-2">
    <label class="block text-sm font-medium text-gray-700" for="title">Activity title</label>
    <input class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" type="text" id="title" name="title" value="{{ old('title', $activity->title) }}" maxlength="255" required>
</div>

<div class="grid min-w-0 gap-2">
    <label class="block text-sm font-medium text-gray-700" for="starts_at">Date and time</label>
    <input class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" type="datetime-local" id="starts_at" name="starts_at"
           value="{{ old('starts_at', $activity->starts_at?->format('Y-m-d\TH:i')) }}"
           min="{{ substr($trip->start_date, 0, 10) }}T00:00"
           max="{{ substr($trip->end_date, 0, 10) }}T23:59"
           aria-describedby="activity-dates" required>
    <p id="activity-dates" class="text-sm text-gray-600">Choose a time between {{ substr($trip->start_date, 0, 10) }} and {{ substr($trip->end_date, 0, 10) }}. Use local time at your destination.</p>
</div>

<div class="grid min-w-0 gap-2">
    <label class="block text-sm font-medium text-gray-700" for="location">Location (optional)</label>
    <input class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" type="text" id="location" name="location" value="{{ old('location', $activity->location) }}" maxlength="255">
</div>

<div class="grid min-w-0 gap-2">
    <label class="block text-sm font-medium text-gray-700" for="notes">Notes (optional)</label>
    <textarea class="block w-full min-w-0 rounded-md border-gray-400 shadow-xs focus:border-indigo-500 focus:ring-indigo-500" id="notes" name="notes" rows="4" maxlength="5000">{{ old('notes', $activity->notes) }}</textarea>
</div>
