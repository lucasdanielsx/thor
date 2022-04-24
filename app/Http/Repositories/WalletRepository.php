<?php

namespace App\Http\Repositories;

use App\Models\Wallet;

class WalletRepository
{
    public function updateBalanceIn(string $id, int $value)
    {
        $wallet = Wallet::where('id', $id)->first();
        $wallet->balance = $wallet->balance + $value;
        $wallet->save();

        return $wallet;
    }

    public function updateBalanceOut(string $id, int $value)
    {
        $wallet = Wallet::where('id', $id)->first();
        $wallet->balance = $wallet->balance - $value;
        $wallet->save();

        return $wallet;
    }
}