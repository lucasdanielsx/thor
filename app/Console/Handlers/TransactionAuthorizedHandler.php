<?php

namespace App\Console\Handlers;

use App\Console\Services\TransactionServiceHandler;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use App\Exceptions\HandlerException;
use App\Models\Transaction;
use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use Illuminate\Support\Facades\Log;

class TransactionAuthorizedHandler extends BaseHandler
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
            $transaction->status == TransactionStatus::PAID || 
            $transaction->status == TransactionStatus::NOT_PAID
        ) 
          throw new HandlerException('Invalid transaction status');

        //TODO valid events
    }

    public function __invoke(KafkaConsumerMessage $message) {
        $this->validRetries($message, Topics::TRANSACTION_AUTHORIZED_DLQ);

        $body = $message->getBody();
        $headers = $message->getHeaders();

        $correlationId = (string) $headers['correlationId'];

        try {
            Log::channel('stderr')->info('Processing message ' . $correlationId);
            
            $transaction = $this->transactionService->findById((string) $body['transactionId']);

            $this->validateTransaction($transaction);

            $this->transactionService->confirmPayment($transaction->id);

            Log::channel('stderr')->info('Message ' . $correlationId . ' processed');
            
            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::TRANSACTION_AUTHORIZED,
                $correlationId,
                $body,
                (int) $headers['retry']
            );

            Log::channel('stderr')->error('Error processing message -> ' . $correlationId);
            
            throw $th;
        }
    }
}