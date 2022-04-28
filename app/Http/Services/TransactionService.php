<?php

namespace App\Http\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidUserToTransactionException;
use App\Exceptions\PayeeNotFoundException;
use App\Exceptions\PayerNotFoundException;
use App\Exceptions\TransactionNotFoundException;
use App\Exceptions\UserNotFoundException;
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

    /**
     * Payer rules to transaction
     */
    private function validatePayer(string $payer, TransactionRequest $request) {
        try {
            $payerUser = $this->userService->findByDocument($payer);
            if($payerUser->type == UserType::Store->value) 
                throw new InvalidUserToTransactionException($payer);
            
            if($payerUser->wallet->balance < $request->value) 
                throw new InsufficientBalanceException($request->payer);
        } catch (UserNotFoundException $th) {
            throw new PayerNotFoundException($payer);
        }

        return $payerUser;
    }

    /**
     * Payee rules to transaction
     */
    private function validatePayee(string $payee) {
        $payeeUser = $this->userService->findByDocument($payee);

        if(!$payeeUser) throw new PayeeNotFoundException($payee);

        return $payeeUser;
    }

    private function sendToAuthorizeTransactionTopic(
        Transaction $transaction, 
        string $correlationId
    ) {
        $body = new TransactionMessage();
        $body->transactionId = $transaction->id;

        $this->kafkaService->publish(
            Topics::AuthorizeTransaction->value, 
            $correlationId,
            (array) $body
        );
    }
    
    /**
     * Process a new transaction
     * 
     * @param TransactionRequest $request
     * @return Transaction
     */
    public function create(TransactionRequest $request)
    {
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info('Creating a new transaction ', [$correlationId]);
        
        $payerUser = $this->validatePayer($request->payer, $request);
        $payeeUser = $this->validatePayee($request->payee);

        //TODO other validations like transaction already done or minimum minutes

        DB::beginTransaction();

        try {
            $transaction = $this->transactionRepository->create($request->value, $request->toArray());

            $this->statementService->create(
                $transaction->value, 
                $payerUser->wallet->id, 
                $transaction->id,
                StatementType::Out,
                $payerUser->wallet->balance
            );

            $this->statementService->create(
                $transaction->value, 
                $payeeUser->wallet->id, 
                $transaction->id,
                StatementType::In
            );

            $this->walletService->decreaseBalance(
                $payerUser->wallet->id, 
                $transaction->value
            );

            DB::commit();

            //TODO catch not sent error
            $this->sendToAuthorizeTransactionTopic($transaction, $correlationId);

            Log::channel('stderr')->info($correlationId . " -> Transaction {$transaction->id} created");

            return $transaction;
        } catch (\Exception $ex) {
            DB::rollback();

            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);
            
            throw $ex;
        }
    }

    /**
     * @param string $id -> transaction id
     * @return \App\Models\Transaction
     */
    public function findById(string $id){
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info($correlationId . ' -> Finding transaction id: ' . $id);

        try {
            $transaction = $this->transactionRepository->findById($id);

            if(!$transaction) throw new TransactionNotFoundException($id);

            Log::channel('stderr')->info($correlationId . ' -> Transaction ' . $id . ' found');

            return $transaction;
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);
              
            throw $ex;
        }
    }

    //TODO remove this return array return
    private function processFinishedStatement($statements){  
        $payeeId = '';
        $payerId = '';

        foreach ($statements as $statement) {
            if($statement->type == StatementType::In) {
                $wallet = $this->walletService->increaseBalance(
                    $statement->wallet->id, 
                    $statement->value
                );

                $this->statementService->updateBalancesAndStatus(
                    $statement->id,
                    $wallet->balance - $statement->value,
                    $wallet->balance,
                    StatementStatus::Finished
                );

                $payeeId = $statement->wallet->user->id;
            } else {
                $this->statementService->updateStatus(
                    $statement->id,
                    StatementStatus::Finished
                );

                $payerId = $statement->wallet->user->id;
            }
        }

        return [$payeeId, $payerId];
    }

    private function notifyUser(
        string $userId, 
        string $message, 
        string $transactionId, 
        string $correlationId
    ) {
        $body = new TransactionNotificationMessage();
        $body->userId = $userId;
        $body->message = $message;
        $body->transactionId = $transactionId;

        $this->kafkaService->publish(
            Topics::TransactionNotification->value, 
            $correlationId, 
            $body->toArray()
        );
    }

    /**
     * Confirm payment of transaction
     * 
     * @param string $id -> transaction id
     * @return \App\Models\Transaction
     */
    public function confirmTransaction(string $id) {
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info($correlationId . ' -> Confirming payment of transaction id: ' . $id);

        DB::beginTransaction();

        try {
            $transaction = $this->findById($id);

            $this->eventService->create(
                $transaction->id, 
                EventType::TransactionPaid,
                []
            );

            $transaction->status = TransactionStatus::Paid;

            [$payeeId, $payerId] = $this->processFinishedStatement($transaction->statements);

            $transaction->save();

            DB::commit();

            $this->notifyUser($payeeId, "New transfer received", $transaction->id, $correlationId);
            $this->notifyUser($payerId, "Transfer performed successfully", $transaction->id, $correlationId);

            Log::channel('stderr')->info($correlationId . ' -> Transaction ' . $id . ' was confirmed');

            return $transaction;
        } catch (\Exception $ex) {
            DB::rollback();

            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);
            
            throw $ex;
        }
    }

    //TODO remove this return
    private function processNotFinishedStatement($statements){  
        $payerId = '';

        foreach ($statements as $statement) {
            if($statement->type == StatementType::In) {
                $this->walletService->increaseBalance(
                    $statement->wallet->id, 
                    $statement->value
                );

                $this->statementService->updateStatus(
                    $statement->id,
                    StatementStatus::NotFinished
                );
            } else {
                $this->statementService->updateStatus(
                    $statement->id,
                    StatementStatus::NotFinished
                );

                $payerId = $statement->wallet->user->id;
            }
        }

        return $payerId;
    }

    /**
     * Cancel transaction
     * 
     * @param string $id -> transaction id
     * @return \App\Models\Transaction
     */
    public function cancelTransaction(string $id) {
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info($correlationId . ' -> Reversal payment of transaction id: ' . $id);

        DB::beginTransaction();

        try {
            $transaction = $this->findById($id);

            $this->eventService->create(
                $transaction->id, 
                EventType::TransactionNotPaid,
                []
            );

            $transaction->status = TransactionStatus::NotPaid;

            $payerId = $this->processNotFinishedStatement($transaction->statements);

            $transaction->save();

            DB::commit();

            $this->notifyUser($payerId, "Transfer not performed", $transaction->id, $correlationId);

            Log::channel('stderr')->info($correlationId . ' -> Transaction ' . $id . ' was not confirmed');

            return $transaction;
        } catch (\Exception $ex) {
            DB::rollback();

            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);
            
            throw $ex;
        }
    }
}