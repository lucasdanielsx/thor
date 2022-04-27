<?php

namespace App\Http\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidUserToTransactionException;
use App\Exceptions\PayeeNotFoundException;
use App\Exceptions\PayerNotFoundException;
use App\Exceptions\TransactionNotFoundException;
use App\Http\Repositories\TransactionRepository;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Shared\Enums\UserType;
use App\Shared\Enums\StatementType;
use App\Shared\Enums\StatementStatus;
use App\Shared\Enums\EventType;
use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\Topics;
use App\Models\Wallet;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Messages\TransactionMessage;
use App\Shared\Kafka\Messages\TransactionNotificationMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class TransactionService 
{
    private TransactionRepository $transactionRepository;
    private StatementService $statementService;
    private WalletService $walletService;
    private KafkaService $kafkaService;
    private UserService $userService;
    private EventService $eventService;
    
    public function __construct(
        TransactionRepository $transactionRepository,
        StatementService $statementService,
        WalletService $walletService,
        KafkaService $kafkaService,
        UserService $userService, 
        EventService $eventService
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->statementService = $statementService;
        $this->walletService = $walletService;
        $this->kafkaService = $kafkaService;
        $this->userService = $userService;
        $this->eventService = $eventService;
    }

    private function validateUsers(string $payee, string $payer) {
        $payerUser = $this->userService->findByDocument($payer);
        if(!$payerUser) throw new PayerNotFoundException($payer);
        if($payerUser->type === UserType::STORE) 
            throw new InvalidUserToTransactionException($payer);

        $payeeUser = $this->userService->findByDocument($payee);
        if(!$payeeUser) throw new PayeeNotFoundException($payee);

        return [$payeeUser, $payerUser];
    }

    private function validateBalance(TransactionRequest $request, Wallet $wallet){
        if($wallet->balance < $request->value) 
            throw new InsufficientBalanceException($request->payer);
    }

    private function sendToAuthorizeTransactionTopic(
        Transaction $transaction, 
        string $correlationId
    ) {
        $body = new TransactionMessage();
        $body->transactionId = $transaction->id;

        $this->kafkaService->publish(
            Topics::AUTHORIZE_TRANSACTION, 
            $correlationId,
            (array) $body
        );
    }

    public function create(TransactionRequest $request)
    {
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info('Creating a new transaction ', [$correlationId]);

        [$payeeUser, $payerUser] = $this->validateUsers($request->payee, $request->payer);
        
        $this->validateBalance($request, $payerUser->wallet);

        //Other validations like transaction already done or minimum minutes

        DB::beginTransaction();

        try {
            $transaction = $this->transactionRepository->create($request->value, $request->toArray());

            $this->statementService->create(
                $transaction->value, 
                $payerUser->wallet->id, 
                $transaction->id,
                StatementType::OUT,
                $payerUser->wallet->balance
            );

            $this->statementService->create(
                $transaction->value, 
                $payeeUser->wallet->id, 
                $transaction->id,
                StatementType::IN
            );

            $this->walletService->updateBalanceOut(
                $payerUser->wallet->id, 
                $transaction->value
            );

            DB::commit();

            //TODO catch send error
            $this->sendToAuthorizeTransactionTopic($transaction, $correlationId);

            Log::channel('stderr')->info("Transaction {$transaction->id} created", [$correlationId]);

            return $transaction;
        } catch (\Exception $ex) {
            DB::rollback();

            Log::channel('stderr')->error($ex, [$correlationId]);
            
            throw $ex;
        }
    }

    /**
     * @param string $id -> transaction id
     * @return \App\Models\Transaction
     */
    public function findById(string $id){
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info('Finding transaction id: ' . $id, [$correlationId]);

        try {
            $transaction = $this->transactionRepository->findById($id);

            if(!$transaction) throw new TransactionNotFoundException($id);

            Log::channel('stderr')->info('Transaction ' . $id . ' found', [$correlationId]);

            return $transaction;
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$correlationId]);
              
            throw $ex;
        }
    }

    //TODO remove this return
    private function processFinishedStatement($statements){  
        $payeeId = '';
        $payerId = '';

        foreach ($statements as $statement) {
            if($statement->type == StatementType::IN) {
                $wallet = $this->walletService->updateBalanceIn(
                    $statement->wallet->id, 
                    $statement->value
                );

                $this->statementService->updateBalancesAndStatus(
                    $statement->id,
                    $wallet->balance - $statement->value,
                    $wallet->balance,
                    StatementStatus::FINISHED
                );

                $payeeId = $statement->wallet->user->id;
            } else {
                $this->statementService->updateStatus(
                    $statement->id,
                    StatementStatus::FINISHED
                );

                $payerId = $statement->wallet->user->id;
            }
        }

        return [$payeeId, $payerId];
    }

    private function notifyUser(string $userId, string $message, string $transactionId, string $logId){
        $body = new TransactionNotificationMessage();
        $body->userId = $userId;
        $body->message = $message;
        $body->transactionId = $transactionId;

        $this->kafkaService->publish(Topics::TRANSACTION_NOTIFICATION, $logId, $body->toArray());
    }

    /**
     * Confirm payment of transaction
     * 
     * @param string $id -> transaction id
     * @return \App\Models\Transaction
     */
    public function confirmPayment(string $id) {
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info('Confirming payment of transaction id: ' . $id, [$correlationId]);

        DB::beginTransaction();

        try {
            $transaction = $this->findById($id);

            $this->eventService->create(
                $transaction->id, 
                EventType::TRANSACTION_PAID,
                []
            );

            $transaction->status = TransactionStatus::PAID;

            [$payeeId, $payerId] = $this->processFinishedStatement($transaction->statements);

            $transaction->save();

            DB::commit();

            $this->notifyUser($payeeId, "New transfer received", $transaction->id, $correlationId);
            $this->notifyUser($payerId, "Transfer performed successfully", $transaction->id, $correlationId);

            return $transaction;
        } catch (\Exception $ex) {
            DB::rollback();

            Log::channel('stderr')->error($ex, [$correlationId]);
            
            throw $ex;
        }
    }

    //TODO remove this return
    private function processNotFinishedStatement($statements){  
        $payerId = '';

        foreach ($statements as $statement) {
            if($statement->type == StatementType::OUT) {
                $this->walletService->updateBalanceIn(
                    $statement->wallet->id, 
                    $statement->value
                );

                $this->statementService->updateStatus(
                    $statement->id,
                    StatementStatus::NOT_FINISHED
                );
            } else {
                $this->statementService->updateStatus(
                    $statement->id,
                    StatementStatus::NOT_FINISHED
                );

                $payerId = $statement->wallet->user->id;
            }
        }

        return $payerId;
    }

    /**
     * Revert payment
     * 
     * @param string $id -> transaction id
     * @return \App\Models\Transaction
     */
    public function reversalPayment(string $id) {
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info('Reversal payment of transaction id: ' . $id, [$correlationId]);

        DB::beginTransaction();

        try {
            $transaction = $this->findById($id);

            $this->eventService->create(
                $transaction->id, 
                EventType::TRANSACTION_NOT_PAID,
                []
            );

            $transaction->status = TransactionStatus::NOT_PAID;

            $payerId = $this->processNotFinishedStatement($transaction->statements);

            $transaction->save();

            DB::commit();

            $this->notifyUser($payerId, "Transfer not performed", $transaction->id, $correlationId);

            return $transaction;
        } catch (\Exception $ex) {
            DB::rollback();

            Log::channel('stderr')->error($ex, [$correlationId]);
            
            throw $ex;
        }
    }
}