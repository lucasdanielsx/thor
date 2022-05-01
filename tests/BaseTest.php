<?php

namespace Tests;

use App\Models\Event;
use App\Models\Statement;
use App\Models\Transaction;
use App\Models\User;
use App\Shared\Enums\StatementType;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class BaseTest extends TestCase
{
    use RefreshDatabase;

    public function getJsonResponse(string $fileName) {
        $path = storage_path() . '/json-tests/responses/' . $fileName;

        return json_decode(file_get_contents($path), true);
    }

    public function getJsonRequest(string $fileName) {
        $path = storage_path() . '/json-tests/requests/' . $fileName;

        return json_decode(file_get_contents($path), true);
    }

    public function getCostumerUser() {
        return User::where('type', 1)->first();
    }

    public function getStoreUser() {
        return User::where('type', 2)->first();
    }

    public function createTransaction() {
        return Transaction::factory()->create();
    }

    public function createTransactionInPaidStatus() {
        return Transaction::factory()->paidStatus()->create();
    }

    public function createEvent(string $transactionId){
        Event::factory(['transaction_id' => $transactionId])->create();
    }

    public function createStatements(string $transactionId) {
        $customerUser = $this->getCostumerUser();
        $storeUser = $this->getStoreUser();

        Statement::factory(['type' => StatementType::In, 'transaction_id' => $transactionId, 'wallet_id' => $storeUser->wallet->id])->create();
        Statement::factory(['type' => StatementType::Out, 'transaction_id' => $transactionId, 'wallet_id' => $customerUser->wallet->id])->create();
    }
}