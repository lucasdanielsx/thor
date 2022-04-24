<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

class PayeeNotFoundException extends Exception
{
    private string $payee;
      
    public function __construct(string $payee)
    {
        $this->payee = $payee;
    }
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(["error" => "payee {$this->payee} not found"], Response::HTTP_BAD_REQUEST);
    }
}