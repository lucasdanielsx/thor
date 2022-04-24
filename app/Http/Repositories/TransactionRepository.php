<?php

namespace App\Http\Repositories;

use App\Shared\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionRepository 
{
    /**
     * @param int $value -> value transacted
     * @param ?array $payload -> any data about transaction
     * @return Transaction
     */
    public function create(
      int $value, 
      ?array $payload = [])
    {
        return Transaction::create([
          'id' => Str::uuid(),
          'value' => $value,
          'status' => TransactionStatus::CREATED,
          'payload' => json_encode($payload)
      ]);
    }

    /**
     * @param string $id -> transaction id
     * @return Transaction
     */
    public function findById(string $id) {
        return Transaction::where('id', $id)->first();
    }
}