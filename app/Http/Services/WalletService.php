<?php

namespace App\Http\Services;

use App\Http\Repositories\WalletRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletService 
{
    private WalletRepository $walletRepository;
    
    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * deposit money in Wallet
     * 
     * @param string $id -> Id of wallet
     * @param int $value -> value to be + or - of balance
     * @return App\Models\Wallet
     */
    public function updateBalanceIn(string $id, int $value)
    {
        $logId = Str::uuid();

        try {
            Log::info('Update balance of wallet: ' . $id, [$logId]);

            return $this->walletRepository->updateBalanceIn($id, $value);
        } catch (\Exception $ex) {
            Log::error($ex, [$logId]);

            throw $ex;
        }
    }

    /**
     * withdraw money of Wallet
     * 
     * @param string $id -> Id of wallet
     * @param int $value -> value to be + or - of balance
     * @return App\Models\Wallet
     */
    public function updateBalanceOut(string $id, int $value)
    {
        $logId = Str::uuid();

        try {
            Log::info('Update balance of wallet: ' . $id, [$logId]);

            return $this->walletRepository->updateBalanceOut($id, $value);
        } catch (\Exception $ex) {
            Log::error($ex, [$logId]);

            throw $ex;
        }
    }
}