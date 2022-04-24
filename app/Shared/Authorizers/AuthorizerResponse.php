<?php

namespace App\Shared\Authorizers;

class AuthorizerResponse
{
    public string $status;
    public array $payload;

    public function toArray() {
        return [
            'status' => $this->status,
            'payload' => $this->payload
        ];
    }
}