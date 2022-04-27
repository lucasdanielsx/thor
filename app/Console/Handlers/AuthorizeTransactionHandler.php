<?php

namespace App\Console\Handlers;

use App\Console\Services\TransactionServiceHandler;
use App\Exceptions\HandlerException;
use App\Http\Services\EventService;
use App\Models\Transaction;
use App\Shared\Authorizers\AuthorizerResponse;
use App\Shared\Authorizers\IAuthorizer;
use App\Shared\Authorizers\AuthorizerStatus;
use App\Shared\Enums\EventType;
use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\Topics;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Messages\TransactionMessage;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

class AuthorizeTransactionHandler extends BaseHandler
{
    private IAuthorizer $authorizer;
    private TransactionServiceHandler $transactionService;
    private EventService $eventService;
      
    public function __construct(
        TransactionServiceHandler $transactionService,
        EventService $eventService,
        KafkaService $kafkaService,
        IAuthorizer $authorizer
    ) {
        parent::__construct($kafkaService);

        $this->authorizer = $authorizer;
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
            Log::channel('stderr')->info('Processing message ' . $correlationId);

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

            Log::channel('stderr')->info('Message ' . $correlationId . ' processed');

            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::AUTHORIZE_TRANSACTION,
                $correlationId,
                $body,
                (int) $headers['retry']
            );
            
            Log::channel('stderr')->error('Error processing message -> ' . $correlationId);

            throw $th;
        }
    }
}