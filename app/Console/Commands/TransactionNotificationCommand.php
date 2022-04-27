<?php

namespace App\Console\Commands;

use App\Shared\Kafka\Topics;
use App\Console\Handlers\TransactionNotificationHandler;
use App\Http\Services\EventService;
use App\Http\Services\UserService;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Shared\Kafka\KafkaService;
use App\Shared\Notifiers\MockNotifier;

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

    private UserService $userService;
    private EventService $eventService;
    private KafkaService $kafkaService;
      
    public function __construct(
        UserService $userService,
        EventService $eventService,
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
          Log::channel('stderr')->info('Starting command ' . $this->description);

          $consumer = Kafka::createConsumer()->subscribe(Topics::TRANSACTION_NOTIFICATION);

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
