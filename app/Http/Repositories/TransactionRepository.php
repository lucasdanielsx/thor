<?php

namespace App\Http\Repositories;

use App\Shared\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionRepository 
{
    /**
     * Create a new transaction
     * 
     * @param int $value -> Transaction value
     * @param ?array $payload -> any important data
     * @return Transaction
     */
    public function create(
      int $value, 
      ?array $payload = [])
    {
        return Transaction::create([
            'id' => Str::uuid(),
            'value' => $value,
            'status' => TransactionStatus::Created,
            'payload' => json_encode($payload)
        ]);
    }

    /**
     * Find transaction by Id
     * 
     * @param string $id -> transaction id
     * @return Transaction
     */
    public function findById(string $id) {
        return Transaction::where('id', $id)->first();
    }
}