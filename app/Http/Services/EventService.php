<?php

namespace App\Http\Services;

use App\Http\Repositories\EventRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventService 
{
    private EventRepository $eventRepository;
    
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function create(string $transactionId, string $type, ?array $payload = []) {
        $logId = Str::uuid();

        try {
            Log::channel('stderr')->info('Create an statement for transaction: ' . $transactionId, [$logId]);

            return $this->eventRepository->create($transactionId, $type, $payload);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$logId]);

            throw $ex;
        }
    }
}