<?php

namespace App\Console\Handlers;

use App\Exceptions\HandlerException;
use App\Shared\Kafka\KafkaService;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

abstract class BaseHandler
{
    public KafkaService $kafkaService;

    public function __construct(
      KafkaService $kafkaService
    ) {
        $this->kafkaService = $kafkaService;
    }

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
    public function retry(string $topic, string $correlationId, array $body, int $retry){
        $this->kafkaService->publish($topic, $correlationId, $body, $retry + 1);
    }

    public function sendToDlq(string $topic, string $correlationId, array $body){
        $this->kafkaService->publish($topic, $correlationId, $body);
    }
}