<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

class InvalidUserToTransactionException extends Exception
{
    private string $payer;
      
    public function __construct(string $payer)
    {
        $this->payer = $payer;
    }
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(["error" => "payer {$this->payer} can't transaction"], Response::HTTP_FORBIDDEN);
    }
}