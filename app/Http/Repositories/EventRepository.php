<?php

namespace App\Http\Repositories;

use App\Models\Event;
use App\Shared\Enums\EventType;
use Illuminate\Support\Str;

class EventRepository 
{
    /**
     * Create a new event
     * 
     * @param string $transactionId -> Transaction Id
     * @param EventType $type
     * @param ?array $payload -> any important data
     * @return Event
     */
    public function create(string $transactionId, EventType $type, ?array $payload = [])
    {
        return Event::create([
            'id' => Str::uuid(),
            'transaction_id' => $transactionId,
            'type' => $type,
            'payload' => json_encode($payload)
        ]);
    }
}