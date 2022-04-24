<?php

namespace App\Shared\Kafka\Messages;

class TransactionMessage
{
    public string $transactionId;

    public function toArray() {
        return ['transactionId' => $this->transactionId];
    }
}