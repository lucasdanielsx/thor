<?php

namespace App\Shared\Notifiers;

use Illuminate\Support\Facades\Http;

class MockNotifier implements INotifier
{
    private string $uri;
    
    public function __construct()
    {
        $this->uri = env("MOCK_NOTIFIER_URI");
    }

    public function notify(): NotifierResponse {
        $notifierResponse = new NotifierResponse();

        try {
            $response = Http::get($this->uri . '/notify');

            $notifierResponse->payload = $response->json();

            if($response->successful())
                $notifierResponse->status = NotifierStatus::Notified;
            else
                $notifierResponse->status = NotifierStatus::NotNotified;
        } catch (\Throwable $th) {
            $notifierResponse->status = NotifierStatus::Error;
            $notifierResponse->payload = json_encode($th);
        }

        return $notifierResponse;
    }
}