<?php

namespace App\Shared\Notifiers;

class NotifierResponse
{
    public NotifierStatus $status;
    public array $payload;

    public function toArray() {
        return [
            'status' => $this->status,
            'payload' => $this->payload
        ];
    }
}