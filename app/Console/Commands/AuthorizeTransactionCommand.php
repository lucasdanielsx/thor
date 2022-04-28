<?php

namespace App\Console\Commands;

use App\Console\Handlers\AuthorizeTransactionHandler;
use App\Console\Services\EventServiceHandler;
use App\Console\Services\TransactionServiceHandler;
use App\Shared\Authorizers\MockAuthorizer;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Facades\Kafka;

class AuthorizeTransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:authorize_transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Authorize Transaction';

    private TransactionServiceHandler $transactionService;
    private EventServiceHandler $eventService;
    private KafkaService $kafkaService;
      
    public function __construct(
        TransactionServiceHandler $transactionService,
        EventServiceHandler $eventService,
        KafkaService $kafkaService
    ) {
        parent::__construct();

        $this->transactionService = $transactionService;
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

          $consumer = Kafka::createConsumer()->subscribe(Topics::AuthorizeTransaction->value);

          $consumer->withHandler(new AuthorizeTransactionHandler(
              $this->transactionService, 
              $this->eventService,
              $this->kafkaService,
              new MockAuthorizer()
          ));

          $consumer->build()->consume();
      } catch (\Throwable $th) {
          Log::channel('stderr')->error($th);

          throw $th;
      }
    }
}
