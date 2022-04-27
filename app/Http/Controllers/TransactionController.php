<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Services\TransactionService;
use Illuminate\Support\Facades\Request;

class TransactionController extends Controller
{
    private TransactionService $transactionService;
    
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function create(TransactionRequest $request)
    {
        $transaction = $this->transactionService->create($request);

        return response()->json($transaction, 201);
    }

    public function findById(string $id)
    {
        $transaction = $this->transactionService->findById($id);

        return response()->json($transaction, 200);
    }
}
