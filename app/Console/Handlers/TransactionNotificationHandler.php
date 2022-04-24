<?php

namespace App\Console\Handlers;

use App\Http\Services\EventService;
use App\Shared\Kafka\Topics;
use App\Shared\Kafka\KafkaService;
use App\Http\Services\UserService;
use App\Shared\Enums\EventType;
use App\Shared\Notifiers\INotifier;
use App\Shared\Notifiers\MockNotifier;
use App\Shared\Notifiers\NotifierResponse;
use App\Shared\Notifiers\NotifierStatus;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

class TransactionNotificationHandler extends BaseHandler
{
    private INotifier $notifier;
    private UserService $userService;
    private EventService $eventService;
      
    public function __construct(
        UserService $userService,
        KafkaService $kafkaService,
        EventService $eventService
    ) {
        parent::__construct($kafkaService);

        $this->notifier = new MockNotifier();
        $this->userService = $userService;
        $this->eventService = $eventService;
    }

    private function processResponse(NotifierResponse $response) {
        switch($response->status) {
            case NotifierStatus::NOTIFIED:
                return EventType::TRANSACTION_NOTIFIED;
            default:
                return EventType::TRANSACTION_NOT_NOTIFIED;
        }
    }

    public function __invoke(KafkaConsumerMessage $message) {
        $this->validRetries($message, Topics::TRANSACTION_NOTIFICATION_DLQ);

        $body = $message->getBody();
        $headers = $message->getHeaders();

        $correlationId = (string) $headers['correlationId'];

        try {
            $user = $this->userService->findById((string) $body['userId']);

            $response = $this->notifier->notify();
            
            $eventType = $this->processResponse($response);
            
            $this->eventService->create(
                (string) $body['transactionId'], 
                $eventType,
                ["userId" => $user->id, "notifierResponse" => $response->toArray()]
            );

            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::TRANSACTION_NOTIFICATION,
                $correlationId,
                $body,
                (int) $headers['retry']
            );
            
            throw $th;
        }
    }
}