<?php

namespace App\Shared\Enums;

enum EventType: string
{
    case TransactionAuthorized = 'TRANSACTION_AUTHORIZED';
    case TransactionNotAuthorized = 'TRANSACTION_NOT_AUTHORIZED';
    case TransactionPaid = 'TRANSACTION_PAID';
    case TransactionNotPaid = 'TRANSACTION_NOT_PAID';
    case TransactionNotified = 'TRANSACTION_NOTIFIED';
    case TransactionNotNotified = 'TRANSACTION_NOT_NOTIFIED';
}