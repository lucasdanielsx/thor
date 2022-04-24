<?php

namespace App\Shared\Kafka;

class KafkaHeader
{
    public string $correlationId;
    public string $retry;

    public function toArray() {
        return [
            'correlationId' => $this->correlationId,
            'retry' => $this->retry,
        ];
    }
}