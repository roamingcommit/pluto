<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $trips = $request->user()->trips()->get();

        return view('trips.index', ['trips' => $trips]);
    }

    public function show(Request $request, Trip $trip): View
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        $activities = $trip->activities()->orderBy('starts_at')->orderBy('id')->get();

        return view('trips.show', ['trip' => $trip, 'activities' => $activities]);
    }

    public function create(): View
    {
        return view('trips.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'hotel' => ['nullable', 'string', 'max:255'],
        ]);

        $trip = new Trip;
        $trip->title = $validated['title'];
        $trip->start_date = $validated['start_date'];
        $trip->end_date = $validated['end_date'];
        $trip->hotel = $validated['hotel'] ?? null;

        $request->user()->trips()->save($trip);

        return redirect()->route('trips.index');
    }

    public function edit(Request $request, Trip $trip): View
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        return view('trips.edit', ['trip' => $trip]);
    }

    public function update(Request $request, Trip $trip): RedirectResponse
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'hotel' => ['nullable', 'string', 'max:255'],
        ]);

        $hasActivitiesOutsideDates = $trip->activities()
            ->where(function (Builder $query) use ($validated): void {
                $query->whereDate('starts_at', '<', $validated['start_date'])
                    ->orWhereDate('starts_at', '>', $validated['end_date']);
            })->exists();

        if ($hasActivitiesOutsideDates) {
            throw ValidationException::withMessages([
                'end_date' => 'These dates would leave an activity outside the trip. Reschedule or remove that activity first.',
            ]);
        }

        $trip->title = $validated['title'];
        $trip->start_date = $validated['start_date'];
        $trip->end_date = $validated['end_date'];
        $trip->hotel = $validated['hotel'] ?? null;
        $trip->save();

        return redirect()->route('trips.show', $trip);
    }

    public function destroy(Request $request, Trip $trip): RedirectResponse
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        $trip->delete();

        return redirect()->route('trips.index');
    }
}
