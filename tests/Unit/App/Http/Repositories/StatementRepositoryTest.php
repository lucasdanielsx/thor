<?php

namespace Tests\Unit\App\Http\Repositories;

use App\Http\Repositories\StatementRepository;
use App\Shared\Enums\StatementType;
use Illuminate\Database\QueryException;
use Tests\BaseTest;

class StatementRepositoryTest extends BaseTest
{
    public function test_create_success()
    {
        $this->seed();
        $user = $this->getCostumerUser();
        $transaction = $this->createTransaction();

        $repository = new StatementRepository();
        $statement = $repository->create(
            1000,
            $user->wallet->id,
            $transaction->id,
            StatementType::IN
        );

        $this->assertNotEmpty($statement->id);
    }

    public function test_create_without_transaction_error()
    {
        $this->expectException(QueryException::class);
        $this->seed();
        $user = $this->getCostumerUser();

        $repository = new StatementRepository();
        $repository->create(
            1000,
            $user->wallet->id,
            "",
            StatementType::IN
        );
    }

    public function test_create_without_wallet_error()
    {
        $this->expectException(QueryException::class);
        $this->seed();
        $transaction = $this->createTransaction();

        $repository = new StatementRepository();
        $repository->create(
            1000,
            "",
            $transaction->id,
            StatementType::IN
        );
    }
}
