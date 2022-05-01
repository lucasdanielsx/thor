<?php

namespace App\Console\Handlers;

use App\Console\Services\TransactionServiceHandler;
use App\Exceptions\HandlerException;
use App\Http\Repositories\EventRepository;
use App\Http\Repositories\StatementRepository;
use App\Http\Repositories\TransactionRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\WalletRepository;
use App\Http\Services\EventService;
use App\Http\Services\StatementService;
use App\Http\Services\UserService;
use App\Http\Services\WalletService;
use App\Shared\Enums\EventType;
use App\Shared\Enums\StatementStatus;
use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Mockery;
use Tests\BaseTest;

class TransactionNotAuthorizedHandlerTest extends BaseTest
{
    private TransactionServiceHandler $transactionServiceHandler;
    private KafkaService $kafkaService;

    public function __construct() {
        parent::__construct();

        $this->kafkaService = new KafkaService();

        $userService = new UserService(new UserRepository());
        
        $this->transactionServiceHandler = new TransactionServiceHandler(
            new TransactionRepository(),
            new StatementService(new StatementRepository()),
            new WalletService(new WalletRepository(), $userService),
            $this->kafkaService,
            $userService,
            new EventService(new EventRepository())
        );
    }

    public function test_process_success()
    {
        Kafka::fake();

        $this->seed();

        $handler = new TransactionNotAuthorizedHandler(
            $this->transactionServiceHandler,
            $this->kafkaService
        );

        $transaction = $this->createTransaction();
        $this->createEvent($transaction->id);
        $this->createStatements($transaction->id);

        $messageMock = Mockery::mock(KafkaConsumerMessage::class);
        $messageMock->shouldReceive('getHeaders')
            ->twice()
            ->andReturn(["retry" => 0, "correlationId" => "cfcf0c2c-5957-43dc-9530-42ec76ac8df7"]);
        $messageMock->shouldReceive('getBody')
            ->once()
            ->andReturn(["transactionId" => $transaction->id]);

        $handler->__invoke($messageMock);

        Kafka::assertPublishedOn(Topics::TransactionNotification->value);

        //validade transaction status
        $transaction = $this->transactionServiceHandler->findById($transaction->id);
        $this->assertEquals(TransactionStatus::NotPaid->value, $transaction->status);
        $this->assertEquals(EventType::TransactionNotPaid->value, $transaction->events[1]->type);

        //validade new wallet balance
        $customerUser = $this->getCostumerUser();
        $this->assertEquals(1000100, $customerUser->wallet->balance);

        //validade statements status
        $this->assertEquals(StatementStatus::NotFinished->value, $customerUser->wallet->statements[0]->status);

        //validade new wallet balance
        $storeUser = $this->getStoreUser();
        $this->assertEquals(StatementStatus::NotFinished->value, $storeUser->wallet->statements[0]->status);
    }

    public function test_process_invalid_event_error()
    {
        $this->expectException(HandlerException::class);

        $this->seed();

        Kafka::fake();

        $handler = new TransactionNotAuthorizedHandler(
            $this->transactionServiceHandler,
            $this->kafkaService
        );

        $transaction = $this->createTransaction();
        
        $messageMock = Mockery::mock(KafkaConsumerMessage::class);
        $messageMock->shouldReceive('getHeaders')
            ->twice()
            ->andReturn(["retry" => 0, "correlationId" => "cfcf0c2c-5957-43dc-9530-42ec76ac8df7"]);
        $messageMock->shouldReceive('getBody')
            ->once()
            ->andReturn(["transactionId" => $transaction->id]);

        $handler->__invoke($messageMock);
    }

    public function test_process_invalid_transaction_status_error()
    {
        $this->expectException(HandlerException::class);

        $this->seed();

        Kafka::fake();

        $handler = new TransactionNotAuthorizedHandler(
            $this->transactionServiceHandler,
            $this->kafkaService
        );

        $transaction = $this->createTransactionInPaidStatus();
        
        $messageMock = Mockery::mock(KafkaConsumerMessage::class);
        $messageMock->shouldReceive('getHeaders')
            ->twice()
            ->andReturn(["retry" => 0, "correlationId" => "cfcf0c2c-5957-43dc-9530-42ec76ac8df7"]);
        $messageMock->shouldReceive('getBody')
            ->once()
            ->andReturn(["transactionId" => $transaction->id]);

        $handler->__invoke($messageMock);
    }
}