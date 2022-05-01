<?php

namespace App\Console\Handlers;

use App\Console\Services\EventServiceHandler;
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
use App\Shared\Authorizers\AuthorizerResponse;
use App\Shared\Authorizers\AuthorizerStatus;
use App\Shared\Authorizers\IAuthorizer;
use App\Shared\Enums\EventType;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Mockery;
use Tests\BaseTest;

class AuthorizeTransactionHandlerTest extends BaseTest
{
    private TransactionServiceHandler $transactionServiceHandler;
    private KafkaService $kafkaService;
    private EventServiceHandler $eventServiceHandler;

    public function __construct() {
        parent::__construct();

        $this->eventServiceHandler = new EventServiceHandler(new EventRepository());
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

    public function test_process_authorized_success()
    {
        Kafka::fake();

        $this->seed();

        $authorizerResponse = new AuthorizerResponse();
        $authorizerResponse->status = AuthorizerStatus::Authorize;
        $authorizerResponse->payload = [];

        $authorizerMock = Mockery::mock(IAuthorizer::class);
        $authorizerMock->shouldReceive('authorize')
            ->once()
            ->andReturn($authorizerResponse);

        $handler = new AuthorizeTransactionHandler(
            $this->transactionServiceHandler,
            $this->eventServiceHandler,
            $this->kafkaService,
            $authorizerMock
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

        Kafka::assertPublishedOn(Topics::TransactionAuthorized->value);

        //validate event type
        $transaction = $this->transactionServiceHandler->findById($transaction->id);
        $this->assertEquals(EventType::TransactionAuthorized->value, $transaction->events[0]->type);
    }

    public function test_process_not_authorized_success()
    {
        Kafka::fake();

        $this->seed();

        $authorizerResponse = new AuthorizerResponse();
        $authorizerResponse->status = AuthorizerStatus::NotAuthorize;
        $authorizerResponse->payload = [];

        $authorizerMock = Mockery::mock(IAuthorizer::class);
        $authorizerMock->shouldReceive('authorize')
            ->once()
            ->andReturn($authorizerResponse);

        $handler = new AuthorizeTransactionHandler(
            $this->transactionServiceHandler,
            $this->eventServiceHandler,
            $this->kafkaService,
            $authorizerMock
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

        Kafka::assertPublishedOn(Topics::TransactionNotAuthorized->value);

        //validate event
        $transaction = $this->transactionServiceHandler->findById($transaction->id);
        $this->assertEquals(EventType::TransactionNotAuthorized->value, $transaction->events[0]->type);
    }

    public function test_process_invalid_transaction_status_error()
    {
        $this->expectException(HandlerException::class);
      
        Kafka::fake();

        $this->seed();

        $authorizerMock = Mockery::mock(IAuthorizer::class);

        $handler = new AuthorizeTransactionHandler(
            $this->transactionServiceHandler,
            $this->eventServiceHandler,
            $this->kafkaService,
            $authorizerMock
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