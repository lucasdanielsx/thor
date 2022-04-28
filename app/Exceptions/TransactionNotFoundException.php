<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

class TransactionNotFoundException extends Exception
{
    private string $id;
      
    public function __construct(string $id, string $message = "")
    {
        parent::__construct($message);

        $this->id = $id;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(["error" => "Transaction {$this->id} not found"], Response::HTTP_NOT_FOUND);
    }
}