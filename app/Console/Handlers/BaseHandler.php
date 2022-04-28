<?php

namespace App\Console\Handlers;

use App\Exceptions\HandlerException;
use App\Shared\Kafka\KafkaService;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

abstract class BaseHandler
{
    private KafkaService $kafkaService;

    public function __construct(
        KafkaService $kafkaService
    ) {
        $this->kafkaService = $kafkaService;
    }

    /**
     * Valid if the message exceeded max retry attempts
     */
    public function validRetries(KafkaConsumerMessage $message, string $dlqTopic, ?int $maxRetries = 5) {
        if($message->getHeaders()['retry'] > $maxRetries){
            $this->sendToDlq(
                $dlqTopic, 
                $message->getHeaders()['correlationId'], 
                $message->getBody()
            );

            throw new HandlerException("Sent message " . $message->getHeaders()['correlationId'] . " to dql topic");
        }
    }

    /**
     * Resend it to topic to be reprocessed
     */
    public function retry(string $topic, string $correlationId, array $body, int $retry){
        $this->kafkaService->publish($topic, $correlationId, $body, $retry + 1);
    }

    /**
     * Send to topic
     */
    public function sendToTopic(string $topic, string $correlationId, array $body){
        $this->kafkaService->publish($topic, $correlationId, $body);
    }

    /**
     * Send to dlq topic
     */
    public function sendToDlq(string $topic, string $correlationId, array $body){
        $this->kafkaService->publish($topic, $correlationId, $body);
    }
}