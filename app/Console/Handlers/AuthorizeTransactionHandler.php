<?php

namespace App\Console\Handlers;

use App\Exceptions\HandlerException;
use App\Http\Services\TransactionService;
use App\Http\Services\EventService;
use App\Models\Transaction;
use App\Shared\Authorizers\AuthorizerResponse;
use App\Shared\Authorizers\IAuthorizer;
use App\Shared\Authorizers\MockAuthorizer;
use App\Shared\Authorizers\AuthorizerStatus;
use App\Shared\Enums\EventType;
use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\Topics;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Messages\TransactionMessage;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

class AuthorizeTransactionHandler extends BaseHandler
{
    private IAuthorizer $authorizer;
    private TransactionService $transactionService;
    private EventService $eventService;
      
    public function __construct(
        TransactionService $transactionService,
        EventService $eventService,
        KafkaService $kafkaService
    ) {
        parent::__construct($kafkaService);

        $this->authorizer = new MockAuthorizer();
        $this->transactionService = $transactionService;
        $this->eventService = $eventService;
    }

    private function processResponse(AuthorizerResponse $response) {
        switch($response->status) {
            case AuthorizerStatus::AUTHORIZED:
                return [
                    EventType::TRANSACTION_AUTHORIZED, 
                    Topics::TRANSACTION_AUTHORIZED
                ];
            default:
                return [
                    EventType::TRANSACTION_NOT_AUTHORIZED, 
                    Topics::TRANSACTION_NOT_AUTHORIZED
                ];
        }
    }

    private function sendToTopic(
        Transaction $transaction, 
        string $topic,
        string $correlationId
    ) {
        $body = new TransactionMessage();
        $body->transactionId = $transaction->id;

        $this->kafkaService->publish($topic, $correlationId, (array) $body);
    }

    public function __invoke(KafkaConsumerMessage $message) {
        $this->validRetries($message, Topics::AUTHORIZE_TRANSACTION_DLQ);

        $body = $message->getBody();
        $headers = $message->getHeaders();
        
        $correlationId = (string) $headers['correlationId'];

        try {
            $transaction = $this->transactionService->findById($body['transactionId']);

            if($transaction->status != TransactionStatus::CREATED) 
              throw new HandlerException('Invalid transaction status');

            $response = $this->authorizer->authorize();
            
            [$eventType, $topic] = $this->processResponse($response);

            $this->eventService->create(
                $transaction->id,
                $eventType,
                $response->toArray()
            );

            $this->sendToTopic($transaction, $topic, $correlationId);

            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::AUTHORIZE_TRANSACTION,
                $correlationId,
                $body,
                (int) $headers['retry']
            );
            
            throw $th;
        }
    }
}