<?php

namespace App\Http\Services;

use App\Http\Repositories\StatementRepository;
use App\Shared\Enums\StatementStatus;
use App\Shared\Enums\StatementType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StatementService 
{
    private StatementRepository $statementRepository;
    
    public function __construct(StatementRepository $statementRepository)
    {
        $this->statementRepository = $statementRepository;
    }

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
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info($correlationId . ' -> Create an statement for wallet: ' . $walletId);

            return $this->statementRepository->create(
                $value, 
                $walletId, 
                $transactionId, 
                $type, 
                $balance
            );
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
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
    public function updateBalancesAndStatus(string $id, int $oldBalance, int $newBalance, StatementStatus $status)
    {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info($correlationId . ' -> Update statement of wallet: ' . $id);

            return $this->statementRepository->updateBalancesAndStatus(
                $id, 
                $oldBalance, 
                $newBalance,
                $status
            );
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
    }

    /**
     * Update Statement Status
     * 
     * @param string $id -> Statement id
     * @param StatementStatus $status
     * @return Statement
     */
    public function updateStatus(string $id, StatementStatus $status)
    {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info($correlationId . ' -> Update statement of wallet: ' . $id);

            return $this->statementRepository->updateStatus($id, $status);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
    }
}