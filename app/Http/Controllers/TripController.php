<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('trips.show', ['trip' => $trip]);
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

        $trip->title = $validated['title'];
        $trip->start_date = $validated['start_date'];
        $trip->end_date = $validated['end_date'];
        $trip->hotel = $validated['hotel'] ?? null;
        $trip->save();

        return redirect()->route('trips.show', $trip);
    }
}
