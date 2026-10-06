<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function create(Request $request, Trip $trip): View
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        return view('activities.create', ['trip' => $trip, 'activity' => new Activity]);
    }

    public function store(Request $request, Trip $trip): RedirectResponse
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        $validated = $this->validatedData($request, $trip);
        $activity = new Activity;
        $activity->title = $validated['title'];
        $activity->starts_at = $validated['starts_at'];
        $activity->location = $validated['location'] ?? null;
        $activity->notes = $validated['notes'] ?? null;
        $trip->activities()->save($activity);

        return redirect()->route('trips.show', $trip)->with('status', 'Activity added.');
    }

    public function show(Request $request, Trip $trip, Activity $activity): View
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        return view('activities.show', ['trip' => $trip, 'activity' => $activity]);
    }

    public function edit(Request $request, Trip $trip, Activity $activity): View
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        return view('activities.edit', ['trip' => $trip, 'activity' => $activity]);
    }

    public function update(Request $request, Trip $trip, Activity $activity): RedirectResponse
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        $validated = $this->validatedData($request, $trip);
        $activity->title = $validated['title'];
        $activity->starts_at = $validated['starts_at'];
        $activity->location = $validated['location'] ?? null;
        $activity->notes = $validated['notes'] ?? null;
        $activity->save();

        return redirect()->route('trips.activities.show', [$trip, $activity])->with('status', 'Activity updated.');
    }

    public function destroy(Request $request, Trip $trip, Activity $activity): RedirectResponse
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        $activity->delete();

        return redirect()->route('trips.show', $trip)->with('status', 'Activity deleted.');
    }

    /** @return array{title: string, starts_at: string, location?: string|null, notes?: string|null} */
    private function validatedData(Request $request, Trip $trip): array
    {
        $start = substr($trip->start_date, 0, 10).'T00:00';
        $end = substr($trip->end_date, 0, 10).'T23:59';

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['bail', 'required', 'date_format:Y-m-d\TH:i', 'after_or_equal:'.$start, 'before_or_equal:'.$end],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'starts_at.date_format' => 'Choose a valid activity date and time.',
            'starts_at.after_or_equal' => 'The activity must be on or after the trip start date.',
            'starts_at.before_or_equal' => 'The activity must be on or before the trip end date.',
        ]);
    }
}
