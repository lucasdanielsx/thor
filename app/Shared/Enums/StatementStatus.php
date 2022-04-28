<?php

namespace App\Shared\Enums;

enum StatementStatus: string
{
    case Created = 'CREATED';
    case Finished = 'FINISHED';
    case NotFinished = 'NOT_FINISHED';
}