<?php

namespace App\Http\Controllers;

use App\Http\Services\WalletService;

class WalletController extends Controller
{
    private WalletService $walletService;
    
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function findByUserDocument(string $document)
    {
        $transaction = $this->walletService->findByUserDocument($document);

        return response()->json($transaction, 200);
    }
}
