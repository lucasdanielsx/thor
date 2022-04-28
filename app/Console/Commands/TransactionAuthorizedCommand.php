<?php

namespace App\Console\Commands;

use App\Console\Handlers\TransactionAuthorizedHandler;
use App\Console\Services\TransactionServiceHandler;
use App\Shared\Kafka\KafkaService;
use App\Shared\Kafka\Topics;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TransactionAuthorizedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:transaction_authorized';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transaction Authorized';

    private TransactionServiceHandler $transactionService;
    private KafkaService $kafkaService;
      
    public function __construct(
        TransactionServiceHandler $transactionService,
        KafkaService $kafkaService
    ) {
        parent::__construct();

        $this->transactionService = $transactionService;
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

          $consumer = Kafka::createConsumer()->subscribe(Topics::TransactionAuthorized->value);

          $consumer->withHandler(new TransactionAuthorizedHandler(
              $this->transactionService,
              $this->kafkaService,
          ));

          $consumer->build()->consume();
      } catch (\Throwable $th) {
          Log::channel('stderr')->error($th);

          throw $th;
      }
    }
}
