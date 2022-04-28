<?php

namespace App\Http\Repositories;

use App\Shared\Enums\StatementStatus;
use App\Models\Statement;
use App\Shared\Enums\StatementType;
use Illuminate\Support\Str;

class StatementRepository 
{
    /**
     * Create a new statement
     * 
     * @param int $value -> Transaction value
     * @param string $walletId -> Wallet Id
     * @param string $transactionId -> Transaction Id
     * @param StatementType $type
     * @param ?int $balance -> Wallet balance
     * @return Statement
     */
    public function create(
        int $value, 
        string $walletId, 
        string $transactionId,
        StatementType $type,
        ?int $balance = null
    ) {
        return Statement::create([
            'id' => Str::uuid(),
            'wallet_id' => $walletId,
            'transaction_id' => $transactionId,
            'value' => $value,
            'status' => StatementStatus::Created,
            'type' => $type,
            'old_balance' => $balance ? $balance : null,
            'new_balance' => $balance ? $balance - $value : null
        ]);
    }

    /**
     * Update Statement Status
     * 
     * @param string $id -> Statement id
     * @param StatementStatus $status
     * @return Statement
     */
    public function updateStatus(
        string $id,
        StatementStatus $status
    ) {
        return Statement::where('id', $id)->update(['status' => $status]);
    }

    /**
     * Update Statement Status
     * 
     * @param string $id -> Statement id
     * @param int $oldBalance -> Old Wallet Balance
     * @param int $newBalance -> New Wallet Balance
     * @param StatementStatus $status
     * @return Statement
     */
    public function updateBalancesAndStatus(
        string $id, 
        int $oldBalance, 
        int $newBalance, 
        StatementStatus $status
    ) {
        return Statement::where('id', $id)
            ->update([
                'old_balance' => $oldBalance, 
                'new_balance' => $newBalance,
                'status' => $status
            ]);
    }
}