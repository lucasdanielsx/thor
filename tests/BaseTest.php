<?php

namespace Tests;

use App\Models\Transaction;
use App\Models\User;
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
}