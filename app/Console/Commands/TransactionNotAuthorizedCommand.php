<?php

namespace App\Console\Commands;

use App\Shared\Kafka\Topics;
use App\Console\Handlers\TransactionNotAuthorizedHandler;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Services\TransactionService;
use App\Shared\Kafka\KafkaService;

class TransactionNotAuthorizedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:transaction_not_authorized';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transaction Not Authorized';

    private TransactionService $transactionService;
    private KafkaService $kafkaService;
      
    public function __construct(
        TransactionService $transactionService,
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
          Log::info('Starting command ' . $this->description);

          $consumer = Kafka::createConsumer()->subscribe(Topics::TRANSACTION_NOT_AUTHORIZED);

          $consumer->withHandler(new TransactionNotAuthorizedHandler(
              $this->transactionService,
              $this->kafkaService,
          ));

          $consumer->build()->consume();
      } catch (\Throwable $th) {
          Log::error($th);

          throw $th;
      }
    }
}
