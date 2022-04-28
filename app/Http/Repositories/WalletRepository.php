<?php

namespace App\Http\Repositories;

use App\Models\Wallet;

class WalletRepository
{
    /**
     * Increase balance value
     * 
     * @param string $id -> Wallet Id
     * @param int $value -> value to be increase
     */
    public function increaseBalance(string $id, int $value)
    {
        $wallet = Wallet::where('id', $id)->first();
        $wallet->balance = $wallet->balance + $value;
        $wallet->save();

        return $wallet;
    }

    /**
     * Decrease balance value
     * 
     * @param string $id -> Wallet Id
     * @param int $value -> value to be decrease
     */
    public function decreaseBalance(string $id, int $value)
    {
        $wallet = Wallet::where('id', $id)->first();
        $wallet->balance = $wallet->balance - $value;
        $wallet->save();

        return $wallet;
    }
}