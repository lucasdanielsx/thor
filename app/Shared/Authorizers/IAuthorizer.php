<?php

namespace App\Shared\Authorizers;

interface IAuthorizer
{
    public function authorize(): AuthorizerResponse;
}