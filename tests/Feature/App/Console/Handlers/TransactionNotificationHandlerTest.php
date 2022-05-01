<?php

namespace App\Console\Handlers;

use App\Console\Services\EventServiceHandler;
use App\Console\Services\UserServiceHandler;
use App\Http\Repositories\EventRepository;
use App\Http\Repositories\UserRepository;
use App\Shared\Enums\EventType;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use App\Shared\Notifiers\INotifier;
use App\Shared\Notifiers\NotifierResponse;
use App\Shared\Notifiers\NotifierStatus;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Mockery;
use Tests\BaseTest;

class TransactionNotificationHandlerTest extends BaseTest
{
    private UserServiceHandler $userServiceHandler;
    private KafkaService $kafkaService;
    private EventServiceHandler $eventServiceHandler;

    public function __construct() {
        parent::__construct();

        $this->userServiceHandler = new UserServiceHandler(new UserRepository());
        $this->kafkaService = new KafkaService();
        $this->eventServiceHandler = new EventServiceHandler(new EventRepository());

    }

    public function test_process_notified_success()
    {
        Kafka::fake();

        $this->seed();

        $notifierResponse = new NotifierResponse();
        $notifierResponse->status = NotifierStatus::Notified;
        $notifierResponse->payload = [];

        $notifierMock = Mockery::mock(INotifier::class);
        $notifierMock->shouldReceive('notify')
            ->once()
            ->andReturn($notifierResponse);

        $handler = new TransactionNotificationHandler(
            $this->userServiceHandler,
            $this->kafkaService,
            $this->eventServiceHandler,
            $notifierMock
        );

        $transaction = $this->createTransaction();
        $customerUser = $this->getCostumerUser();

        $messageMock = Mockery::mock(KafkaConsumerMessage::class);
        $messageMock->shouldReceive('getHeaders')
            ->twice()
            ->andReturn(["retry" => 0, "correlationId" => "cfcf0c2c-5957-43dc-9530-42ec76ac8df7"]);
        $messageMock->shouldReceive('getBody')
            ->once()
            ->andReturn(["transactionId" => $transaction->id, "userId" => $customerUser->id]);

        $handler->__invoke($messageMock);

        //validate event
        $transaction = $this->findTransactionById($transaction->id);
        $this->assertEquals(EventType::TransactionNotified->value, $transaction->events[0]->type);
    }

    public function test_process_not_notified_success()
    {
        Kafka::fake();

        $this->seed();

        $notifierResponse = new NotifierResponse();
        $notifierResponse->status = NotifierStatus::NotNotified;
        $notifierResponse->payload = [];

        $notifierMock = Mockery::mock(INotifier::class);
        $notifierMock->shouldReceive('notify')
            ->once()
            ->andReturn($notifierResponse);

        $handler = new TransactionNotificationHandler(
            $this->userServiceHandler,
            $this->kafkaService,
            $this->eventServiceHandler,
            $notifierMock
        );

        $transaction = $this->createTransaction();
        $customerUser = $this->getCostumerUser();

        $messageMock = Mockery::mock(KafkaConsumerMessage::class);
        $messageMock->shouldReceive('getHeaders')
            ->twice()
            ->andReturn(["retry" => 0, "correlationId" => "cfcf0c2c-5957-43dc-9530-42ec76ac8df7"]);
        $messageMock->shouldReceive('getBody')
            ->once()
            ->andReturn(["transactionId" => $transaction->id, "userId" => $customerUser->id]);

        $handler->__invoke($messageMock);

        Kafka::assertPublishedOn(Topics::TransactionNotification->value);

        //validate event
        $transaction = $this->findTransactionById($transaction->id);
        $this->assertEquals(EventType::TransactionNotNotified->value, $transaction->events[0]->type);
    }
}