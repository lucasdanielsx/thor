<?php

namespace App\Console\Handlers;

use App\Console\Services\TransactionServiceHandler;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use App\Exceptions\HandlerException;
use App\Models\Transaction;
use App\Shared\Enums\EventType;
use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use Illuminate\Support\Facades\Log;

class TransactionNotAuthorizedHandler extends BaseHandler
{
    private TransactionServiceHandler $transactionService;
      
    public function __construct(
        TransactionServiceHandler $transactionService,
        KafkaService $kafkaService
    ) {
        parent::__construct($kafkaService);

        $this->transactionService = $transactionService;
    }

    private function validateTransaction(Transaction $transaction){
        if (
            $transaction->status == TransactionStatus::Paid->value || 
            $transaction->status == TransactionStatus::NotPaid->value
        ) 
          throw new HandlerException('Invalid transaction status');

        if (empty($transaction->events->toArray()) 
            || in_array(EventType::TransactionNotAuthorized->value, array_column($transaction->events->toArray()[0], 'type'))) 
            throw new HandlerException('Invalid transaction authorized event not found');
    }

    public function __invoke(KafkaConsumerMessage $message) {
        $this->validRetries($message, Topics::TransactionNotAuthorizedDlq->value);

        $body = $message->getBody();
        $headers = $message->getHeaders();

        $correlationId = (string) $headers['correlationId'];

        try {
            Log::channel('stderr')->info($correlationId . ' -> processing message');

            $transaction = $this->transactionService->findById((string) $body['transactionId']);

            $this->validateTransaction($transaction);

            $this->transactionService->cancelTransaction($transaction->id);

            Log::channel('stderr')->info($correlationId . ' -> message was processed');
            
            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::TransactionNotAuthorized->value,
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