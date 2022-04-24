<?php

namespace App\Console\Handlers;

use Junges\Kafka\Contracts\KafkaConsumerMessage;
use App\Exceptions\HandlerException;
use App\Http\Services\TransactionService;
use App\Models\Transaction;
use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;

class TransactionNotAuthorizedHandler extends BaseHandler
{
    private TransactionService $transactionService;
      
    public function __construct(
        TransactionService $transactionService,
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
        $this->validRetries($message, Topics::TRANSACTION_NOT_AUTHORIZED_DLQ);

        $body = $message->getBody();
        $headers = $message->getHeaders();

        $correlationId = (string) $headers['correlationId'];

        try {
            $transaction = $this->transactionService->findById((string) $body['transactionId']);

            $this->validateTransaction($transaction);

            $this->transactionService->reversalPayment($transaction->id);

            return true;
        } catch (\Throwable $th) {
            $this->retry(
                Topics::TRANSACTION_NOT_AUTHORIZED,
                $correlationId,
                $body,
                (int) $headers['retry']
            );
            
            throw $th;
        }
    }
}