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
     * Increase balance value
     * 
     * @param string $id -> Wallet Id
     * @param int $value -> value to be increase
     */
    public function increaseBalance(string $id, int $value) {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info($correlationId . ' -> Update balance of wallet: ' . $id);

            return $this->walletRepository->increaseBalance($id, $value);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
    }

    /**
     * Decrease balance value
     * 
     * @param string $id -> Wallet Id
     * @param int $value -> value to be decrease
     */
    public function decreaseBalance(string $id, int $value) {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info($correlationId . ' -> Update balance of wallet: ' . $id);

            return $this->walletRepository->decreaseBalance($id, $value);
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
    }

    /**
     * Find an user by document
     * 
     * @param string $document -> document value of user
     * @return User
     */
    public function findByUserDocument(string $document) {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info($correlationId . ' -> Finding of wallet by document: ' . $document);

            $user = $this->userService->findByDocument($document);
            $user->wallet->statements;

            return $user->wallet;
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
    }
}