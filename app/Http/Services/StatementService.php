<?php

namespace App\Http\Services;

use App\Http\Repositories\StatementRepository;
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
     * Create an statement
     * 
     * @param int $value -> value transacted
     * @param string $walletId -> id of wallet
     * @param string $transactionId -> id of transaction
     * @param int $type -> StatementType
     * @param ?int $balance -> balance of wallet
     * @return App\Models\Wallet
     */
    public function create(
        int $value, 
        string $walletId, 
        string $transactionId,
        int $type,
        ?int $balance = null
    ) {
        $logId = Str::uuid();

        try {
            Log::channel('stderr')->info('Create an statement for wallet: ' . $walletId, [$logId]);

            return $this->statementRepository->create($value, $walletId, $transactionId, $type, $balance);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$logId]);

            throw $ex;
        }
    }

    public function updateBalancesAndStatus(string $id, int $oldBalance, int $newBalance, string $status)
    {
        $logId = Str::uuid();

        try {
            Log::channel('stderr')->info('Update statement of wallet: ' . $id, [$logId]);

            return $this->statementRepository->updateBalancesAndStatus(
                $id, 
                $oldBalance, 
                $newBalance,
                $status
            );
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$logId]);

            throw $ex;
        }
    }

    public function updateStatus(string $id, string $status)
    {
        $logId = Str::uuid();

        try {
            Log::channel('stderr')->info('Update statement of wallet: ' . $id, [$logId]);

            return $this->statementRepository->updateStatus($id, $status);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$logId]);

            throw $ex;
        }
    }
}