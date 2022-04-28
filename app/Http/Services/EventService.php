<?php

namespace App\Http\Services;

use App\Http\Repositories\EventRepository;
use App\Shared\Enums\EventType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventService 
{
    private EventRepository $eventRepository;
    
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Create a new event
     * 
     * @param string $transactionId -> Transaction Id
     * @param EventType $type
     * @param ?array $payload -> any important data
     * @return Event
     */
    public function create(string $transactionId, EventType $type, ?array $payload = []) {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')
                ->info($correlationId . ' -> Create an statement for transaction: ' . $transactionId);

            return $this->eventRepository->create($transactionId, $type, $payload);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
    }
}