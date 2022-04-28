<?php

namespace App\Shared\Enums;

enum TransactionStatus: string
{
    case Paid = 'PAID';
    case Created = 'CREATED';
    case NotPaid = 'NOT_PAID';
}