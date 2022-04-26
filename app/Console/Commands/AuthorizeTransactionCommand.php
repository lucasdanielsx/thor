<?php

namespace App\Console\Commands;

use App\Shared\Kafka\Topics;
use App\Console\Handlers\AuthorizeTransactionHandler;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Services\TransactionService;
use App\Http\Services\EventService;
use App\Shared\Kafka\KafkaService;

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

    private TransactionService $transactionService;
    private EventService $eventService;
    private KafkaService $kafkaService;
      
    public function __construct(
        TransactionService $transactionService,
        EventService $eventService,
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
          Log::channel('stderr')->info('Starting command ' . $this->description);

          $consumer = Kafka::createConsumer()->subscribe(Topics::AUTHORIZE_TRANSACTION);

          $consumer->withHandler(new AuthorizeTransactionHandler(
              $this->transactionService, 
              $this->eventService,
              $this->kafkaService,
          ));

          $consumer->build()->consume();
      } catch (\Throwable $th) {
          Log::channel('stderr')->error($th);

          throw $th;
      }
    }
}
