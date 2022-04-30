<?php

namespace App\Shared\Authorizers;

enum AuthorizerStatus: string
{
    case Authorize = 'AUTHORIZED';
    case NotAuthorize = 'NOT_AUTHORIZED';
    case Error = 'ERROR';
}