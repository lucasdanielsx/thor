<?php

namespace App\Shared\Authorizers;

use Illuminate\Support\Facades\Http;

class MockAuthorizer implements IAuthorizer
{
    private string $uri;
    
    public function __construct()
    {
        $this->uri = env('MOCK_AUTHORIZER_URI');
    }

    public function authorize(): AuthorizerResponse {
        $authorizerResponse = new AuthorizerResponse();

        try {
            $response = Http::get($this->uri . '/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6');

            $authorizerResponse->payload = $response->json();

            if($response->successful())
                $authorizerResponse->status = AuthorizerStatus::AUTHORIZED;
            else
                $authorizerResponse->status = AuthorizerStatus::NOT_AUTHORIZED;
        } catch (\Throwable $th) {
            $authorizerResponse->status = AuthorizerStatus::ERROR;
            $authorizerResponse->payload = json_encode($th);
        }

        return $authorizerResponse;
    }
}