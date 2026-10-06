@if ($errors->any())
    <ul role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div>
    <label for="title">Activity title</label>
    <input type="text" id="title" name="title" value="{{ old('title', $activity->title) }}" maxlength="255" required>
</div>

<div>
    <label for="starts_at">Date and time</label>
    <input type="datetime-local" id="starts_at" name="starts_at"
           value="{{ old('starts_at', $activity->starts_at?->format('Y-m-d\TH:i')) }}"
           min="{{ substr($trip->start_date, 0, 10) }}T00:00"
           max="{{ substr($trip->end_date, 0, 10) }}T23:59"
           aria-describedby="activity-dates" required>
    <p id="activity-dates">Choose a time between {{ substr($trip->start_date, 0, 10) }} and {{ substr($trip->end_date, 0, 10) }}. Use local time at your destination.</p>
</div>

<div>
    <label for="location">Location (optional)</label>
    <input type="text" id="location" name="location" value="{{ old('location', $activity->location) }}" maxlength="255">
</div>

<div>
    <label for="notes">Notes (optional)</label>
    <textarea id="notes" name="notes" rows="4" maxlength="5000">{{ old('notes', $activity->notes) }}</textarea>
</div>
