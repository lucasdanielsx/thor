<?php

namespace Tests\Unit\App\Http\Repositories;

use App\Http\Repositories\EventRepository;
use App\Shared\Enums\EventType;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Tests\BaseTest;

class EventRepositoryTest extends BaseTest
{
    public function test_create_success()
    {
        $this->seed();
        $transaction = $this->createTransaction();

        $repository = new EventRepository();
        $event = $repository->create(
            $transaction->id,
            EventType::TransactionAuthorized,
            ["test" => "test"]
        );

        $this->assertNotEmpty($event->id);
    }

    public function test_create_without_transaction_error()
    {
        $this->expectException(QueryException::class);

        $repository = new EventRepository();
        $repository->create(
            Str::uuid(),
            EventType::TransactionAuthorized,
            ["test" => "test"]
        );
    }
}
