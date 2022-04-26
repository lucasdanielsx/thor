<?php

namespace Tests\Unit\App\Http\Repositories;

use App\Http\Repositories\TransactionRepository;
use Illuminate\Database\QueryException;
use Tests\BaseTest;

class TransactionRepositoryTest extends BaseTest
{
    public function test_create_success()
    {
        $repository = new TransactionRepository();
        $transaction = $repository->create(
            1000,
            ["test" => "test"]
        );

        $this->assertNotEmpty($transaction->id);
    }

    public function test_find_by_id_success()
    {
        $this->seed();
        $transaction = $this->createTransaction();

        $repository = new TransactionRepository();
        $transaction = $repository->findById($transaction->id);

        $this->assertNotEmpty($transaction);
    }

    public function test_find_by_id_empty_id_error()
    {
        $this->expectException(QueryException::class);

        $repository = new TransactionRepository();
        $transaction = $repository->findById("");

        $this->assertNotEmpty($transaction);
    }

    public function test_find_by_id_invalid_id_error()
    {
        $repository = new TransactionRepository();
        $transaction = $repository->findById("55b66785-29c3-4966-abbc-f8ae043fa94d");

        $this->assertEmpty($transaction);
    }
}
