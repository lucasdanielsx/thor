<?php

namespace App\Http\Repositories;

use App\Shared\Enums\StatementStatus;
use App\Models\Statement;
use Illuminate\Support\Str;

class StatementRepository 
{
    public function create(
        int $value, 
        string $walletId, 
        string $transactionId,
        int $type,
        ?int $balance = null
    ) {
        return Statement::create([
            'id' => Str::uuid(),
            'wallet_id' => $walletId,
            'transaction_id' => $transactionId,
            'value' => $value,
            'status' => StatementStatus::CREATED,
            'type' => $type,
            'old_balance' => $balance ? $balance : null,
            'new_balance' => $balance ? $balance - $value : null
        ]);
    }

    public function updateStatus(
        string $id,
        string $status
    ) {
        return Statement::where('id', $id)->update(['status' => $status]);
    }

    public function updateBalancesAndStatus(
        string $id, 
        int $oldBalance, 
        int $newBalance, 
        string $status
    ) {
        return Statement::where('id', $id)
            ->update([
                'old_balance' => $oldBalance, 
                'new_balance' => $newBalance,
                'status' => $status
            ]);
    }
}