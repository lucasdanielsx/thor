<?php

namespace App\Console\Commands;

use App\Console\Handlers\TransactionNotificationHandler;
use App\Console\Services\EventServiceHandler;
use App\Console\Services\UserServiceHandler;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use App\Shared\Notifiers\MockNotifier;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TransactionNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:transaction_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transaction Notification';

    private UserServiceHandler $userService;
    private EventServiceHandler $eventService;
    private KafkaService $kafkaService;
      
    public function __construct(
        UserServiceHandler $userService,
        EventServiceHandler $eventService,
        KafkaService $kafkaService
    ) {
        parent::__construct();

        $this->userService = $userService;
        $this->eventService = $eventService;
        $this->kafkaService = $kafkaService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      try {
          Log::channel('stderr')->info('Starting command -> ' . $this->description);

          $consumer = Kafka::createConsumer()->subscribe(Topics::TransactionNotification->value);

          $consumer->withHandler(new TransactionNotificationHandler(
              $this->userService, 
              $this->kafkaService,
              $this->eventService,
              new MockNotifier()
          ));

          $consumer->build()->consume();
      } catch (\Throwable $th) {
          Log::channel('stderr')->error($th);

          throw $th;
      }
    }
}
