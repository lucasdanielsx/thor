<?php

namespace App\Shared\Kafka;

use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Illuminate\Support\Facades\Log;

class KafkaService
{
    /**
     * Publish a message at kafka
     * @param Topic $topic -> Topic name
     * @param string $logId -> correlation id
     * @param array $body -> message body
     */
    public function publish(
        string $topic, 
        string $correlationId, 
        array $body, 
        ?int $retry = 0
    ) {
        try {
            $header = new KafkaHeader();
            $header->correlationId = $correlationId;
            $header->retry = $retry;
            
            $message = new Message(headers: $header->toArray(), body: $body);
        
            $producer = Kafka::publishOn($topic)->withMessage($message);

            $producer->send();
        } catch (\Throwable $th) {
            Log::error($th);

            throw $th;
        }
    }
}