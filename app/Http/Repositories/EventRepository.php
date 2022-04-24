<?php

namespace App\Http\Repositories;

use App\Models\Event;
use Illuminate\Support\Str;

class EventRepository 
{
    public function create(string $transactionId, string $type, ?array $payload = [])
    {
        return Event::create([
            'id' => Str::uuid(),
            'transaction_id' => $transactionId,
            'type' => $type,
            'payload' => json_encode($payload)
        ]);
    }
}