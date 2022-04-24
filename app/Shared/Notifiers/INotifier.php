<?php

namespace App\Shared\Notifiers;

interface INotifier
{
    public function notify(): NotifierResponse;
}