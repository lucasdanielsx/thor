<?php

namespace App\Shared\Kafka\Messages;

class TransactionNotificationMessage extends TransactionMessage
{
    public string $userId;
    public string $message;

    public function toArray() {
        
        return [
            'userId' => $this->userId,
            'message' => $this->message,
            'transactionId' => $this->transactionId
        ];
    }
}