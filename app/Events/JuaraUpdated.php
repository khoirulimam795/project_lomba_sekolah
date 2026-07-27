<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JuaraUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $eventId,
        public string $message = 'Klasemen juara diperbarui',
    ) {}

    // channel PUBLIK (tanpa auth) — penonton nggak perlu login
    public function broadcastOn(): array
    {
        return [new Channel('event.' . $this->eventId)];
    }

    public function broadcastAs(): string
    {
        return 'JuaraUpdated';
    }

    public function broadcastWith(): array
    {
        return ['eventId' => $this->eventId, 'message' => $this->message];
    }
}