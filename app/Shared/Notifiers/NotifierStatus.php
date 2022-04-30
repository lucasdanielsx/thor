<?php

namespace App\Shared\Notifiers;

enum NotifierStatus: string
{
    case Notified = 'NOTIFIED';
    case NotNotified = 'NOT_NOTIFIED';
    case Error = 'ERROR';
}