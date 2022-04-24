<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

class UserNotFoundException extends Exception
{
    private string $value;
      
    public function __construct(string $value)
    {
        parent::__construct("User {$value} not found");

        $this->value = $value;
    }
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(["error" => "User {$this->value} not found"], Response::HTTP_NOT_FOUND);
    }
}