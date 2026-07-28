<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->get();

        return inertia('Admin/Events/Index', [
            'events' => $events,
        ]);
    }

    public function create()
    {
        return inertia('Admin/Events/Form', [
            'event' => null,
        ]);
    }

    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['nama']) . '-' . Str::lower(Str::random(6));
        $data['created_by'] = Auth::id();

        Event::create($data);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat.')
            ->setStatusCode(303);
    }

    public function edit(Event $event)
    {
        return inertia('Admin/Events/Form', [
            'event' => $event,
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $data = $request->validated();

        $event->update($data);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil diupdate.')
            ->setStatusCode(303);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus.')
            ->setStatusCode(303);
    }
}