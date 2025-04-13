<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->paginate(10);
        return view('tenant.events.index', compact('events'));
    }

    public function create()
    {
        return view('tenant.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
        ]);

        Event::create($validated);

        return redirect()->route('tenant.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event)
    {
        return view('tenant.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('tenant.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
        ]);

        $event->update($validated);

        return redirect()->route('tenant.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('tenant.events.index')
            ->with('success', 'Event deleted successfully.');
    }
} 