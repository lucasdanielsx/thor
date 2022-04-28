<?php

namespace App\Console\Handlers;

use App\Console\Services\EventServiceHandler;
use App\Console\Services\UserServiceHandler;
use App\Shared\Enums\EventType;
use App\Shared\Kafka\Topics;
use App\Shared\Kafka\KafkaService;
use App\Shared\Notifiers\INotifier;
use App\Shared\Notifiers\NotifierResponse;
use App\Shared\Notifiers\NotifierStatus;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Illuminate\Support\Facades\Log;

class TransactionNotificationHandler extends BaseHandler
{
    private INotifier $notifier;
    private UserServiceHandler $userService;
    private EventServiceHandler $eventService;
      
    public function __construct(
        UserServiceHandler $userService,
        KafkaService $kafkaService,
        EventServiceHandler $eventService,
        INotifier $notifier
    ) {
        parent::__construct($kafkaService);

        $this->notifier = $notifier;
        $this->userService = $userService;
        $this->eventService = $eventService;
    }

    private function processResponse(NotifierResponse $response) {
        switch($response->status) {
            case NotifierStatus::Notified:
                return EventType::TransactionNotified;
            default:
                return EventType::TransactionNotNotified;
        }
    }

    public function __invoke(KafkaConsumerMessage $message) {
        $this->validRetries($message, Topics::TransactionNotificationDlq->value);

        $body = $message->getBody();
        $headers = $message->getHeaders();

        $correlationId = (string) $headers['correlationId'];

        try {
            Log::channel('stderr')->info('Processing message ' . $correlationId);

            $user = $this->userService->findById((string) $body['userId']);

            $response = $this->notifier->notify();
            
            $eventType = $this->processResponse($response);
            
            $this->eventService->create(
                (string) $body['transactionId'], 
                $eventType,
                ["userId" => $user->id, "notifierResponse" => $response->toArray()]
            );

            Log::channel('stderr')->info('Message ' . $correlationId . ' processed');
            
            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::TransactionNotification->value,
                $correlationId,
                $body,
                (int) $headers['retry']
            );

            Log::channel('stderr')->error('Error processing message -> ' . $correlationId);
            
            throw $th;
        }
    }
}