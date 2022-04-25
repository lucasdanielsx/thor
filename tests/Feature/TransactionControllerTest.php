<?php

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\Response;
use Tests\BaseTest;

class TransactionControllerTest extends BaseTest
{
    public function test_transactions_without_payload_error()
    {
        $response = $this->postJson('/api/v1/transactions');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson($this->getJsonResponse('test_transactions_without_payload_error.json'));
    }

    public function test_transactions_with_invalid_fields_error()
    {
        $response = $this->postJson('/api/v1/transactions', $this->getJsonRequest('test_transactions_with_invalid_fields_error.json'));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson($this->getJsonResponse('test_transactions_with_invalid_fields_error.json'));
    }
}
