<?php

namespace App\Http\Services;

use App\Http\Repositories\WalletRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletService 
{
    private WalletRepository $walletRepository;
    private UserService $userService;

    public function __construct(
        WalletRepository $walletRepository,
        UserService $userService
    ) {
        $this->walletRepository = $walletRepository;
        $this->userService = $userService;
    }

    /**
     * deposit money in Wallet
     * 
     * @param string $id -> Id of wallet
     * @param int $value -> value to be + or - of balance
     * @return App\Models\Wallet
     */
    public function updateBalanceIn(string $id, int $value) {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info('Update balance of wallet: ' . $id, [$correlationId]);

            return $this->walletRepository->updateBalanceIn($id, $value);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$correlationId]);

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
    public function updateBalanceOut(string $id, int $value) {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info('Update balance of wallet: ' . $id, [$correlationId]);

            return $this->walletRepository->updateBalanceOut($id, $value);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$correlationId]);

            throw $ex;
        }
    }

    public function findByUserDocument(string $document) {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info('Finding of wallet by document: ' . $document, [$correlationId]);

            $user = $this->userService->findByDocument($document);
            $user->wallet->statements;

            return $user->wallet;
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$correlationId]);

            throw $ex;
        }
    }
}