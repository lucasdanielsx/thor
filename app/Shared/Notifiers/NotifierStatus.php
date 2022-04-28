<?php

namespace App\Shared\Notifiers;

enum NotifierStatus: string
{
    const Notified = 'NOTIFIED';
    const NotNotified = 'NOT_NOTIFIED';
    const Error = 'ERROR';
}