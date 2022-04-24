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
                $notifierResponse->status = NotifierStatus::NOTIFIED;
            else
                $notifierResponse->status = NotifierStatus::NOT_NOTIFIED;
        } catch (\Throwable $th) {
            $notifierResponse->status = NotifierStatus::ERROR;
            $notifierResponse->payload = json_encode($th);
        }

        return $notifierResponse;
    }
}