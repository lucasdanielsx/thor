<?php

namespace App\Shared\Authorizers;

class AuthorizerResponse
{
    public AuthorizerStatus $status;
    public array $payload;

    public function toArray() {
        return [
            'status' => $this->status,
            'payload' => $this->payload
        ];
    }
}