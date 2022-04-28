<?php

namespace App\Shared\Authorizers;

enum AuthorizerStatus: string
{
    const Authorize = 'AUTHORIZED';
    const NotAuthorize = 'NOT_AUTHORIZED';
    const Error = 'ERROR';
}