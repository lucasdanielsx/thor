<?php

namespace App\Console\Handlers;

use App\Console\Services\TransactionServiceHandler;
use App\Console\Services\EventServiceHandler;
use App\Exceptions\HandlerException;
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
    private EventServiceHandler $eventService;
      
    public function __construct(
        TransactionServiceHandler $transactionService,
        EventServiceHandler $eventService,
        KafkaService $kafkaService,
        IAuthorizer $authorizer
    ) {
        parent::__construct($kafkaService);

        $this->authorizer = $authorizer;
        $this->transactionService = $transactionService;
        $this->eventService = $eventService;
    }

    /**
     * define event to be insert by authorize response
     */
    private function getEvenType(AuthorizerResponse $response) {
        switch($response->status) {
            case AuthorizerStatus::Authorize:
                return EventType::TransactionAuthorized;
            default:
                return EventType::TransactionNotAuthorized;
        }
    }

    /**
     * define topic to be sent by authorize response
     */
    private function getTopicToBeSent(AuthorizerResponse $response) {
        switch($response->status) {
            case AuthorizerStatus::Authorize:
                return Topics::TransactionAuthorized->value;
            default:
                return Topics::TransactionNotAuthorized->value;
        }
    }

    /**
     * Validate if transaction is ready to be processed
     */
    private function validateTransaction(Transaction $transaction){
        if($transaction->status != TransactionStatus::Created) 
            throw new HandlerException('Invalid transaction status');
    }

    public function __invoke(KafkaConsumerMessage $message) {
        $this->validRetries($message, Topics::AuthorizeTransactionDlq->value);

        $body = $message->getBody();
        $headers = $message->getHeaders();
        
        $correlationId = (string) $headers['correlationId'];

        try {
            Log::channel('stderr')->info($correlationId . ' -> processing message');

            $transaction = $this->transactionService->findById($body['transactionId']);

            $this->validateTransaction($transaction);

            $response = $this->authorizer->authorize();
            
            $eventType = $this->getEvenType($response);
            $topic = $this->getTopicToBeSent($response);

            $this->eventService->create(
                $transaction->id,
                $eventType,
                $response->toArray()
            );

            $message = new TransactionMessage();
            $message->transactionId = $transaction->id;

            $this->sendToTopic($topic, $correlationId, (array) $message);

            Log::channel('stderr')->info($correlationId . ' -> message was processed');

            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::AuthorizeTransaction->value,
                $correlationId,
                $body,
                (int) $headers['retry']
            );
            
            Log::channel('stderr')->error($th);
            Log::channel('stderr')->error('Error processing message -> ' . $correlationId);

            throw $th;
        }
    }
}