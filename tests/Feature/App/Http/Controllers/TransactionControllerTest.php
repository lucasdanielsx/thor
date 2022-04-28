<?php

namespace Tests\Feature\App\Http\Controllers;;

use App\Shared\Enums\TransactionStatus;
use App\Shared\Kafka\Topics;
use Junges\Kafka\Facades\Kafka;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Tests\BaseTest;

class TransactionControllerTest extends BaseTest
{
    public function test_create_transaction_success()
    {
        $this->seed();
        $customer = $this->getCostumerUser();
        $store = $this->getStoreUser();

        Kafka::fake();

        $requestPayload = $this->getJsonRequest('test_create_transaction_success.json');
        $response = $this->postJson('/api/v1/transactions', $requestPayload );

        Kafka::assertPublishedOn(Topics::AuthorizeTransaction->value);

        $response->assertStatus(Response::HTTP_CREATED);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($content['status'], TransactionStatus::Created->value);
        $this->assertEquals($content['value'], $requestPayload['value']);

        $response = $this->get('/api/v1/wallets/' . $customer->document);

        //Valid payer balance
        $content = json_decode($response->getContent(), true);

        $this->assertEquals($content['balance'], 990000);
        $this->assertNotEmpty($content['statements']);

        //Valid payee balance
        $response = $this->get('/api/v1/wallets/' . $store->document);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($content['balance'], 1000000);
        $this->assertNotEmpty($content['statements']);
    }

    public function test_find_by_id_transaction_success()
    {
        $this->seed();

        $transaction = $this->createTransaction();
        $response = $this->get('/api/v1/transactions/' . $transaction->id);

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($content['id'], $transaction->id);
    }

    public function test_find_by_id_transaction_error()
    {
        $response = $this->get('/api/v1/transactions/' . Str::uuid());

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_create_transactions_without_payload_error()
    {
        $response = $this->postJson('/api/v1/transactions');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_without_payload_error.json')
        );
    }

    public function test_create_transactions_with_invalid_fields_error()
    {
        //Test field value min 1
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_with_invalid_fields_error_1.json')
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_with_invalid_fields_error_1.json')
        );

        //Test field value is integer
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_with_invalid_fields_error_2.json')
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_with_invalid_fields_error_2.json')
        );

        //Test field payer min 11
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_with_invalid_fields_error_3.json')
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_with_invalid_fields_error_3.json')
        );

        //Test field payer max 14
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_with_invalid_fields_error_4.json')
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_with_invalid_fields_error_4.json')
        );

        //Test field payee min 11
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_with_invalid_fields_error_5.json')
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_with_invalid_fields_error_5.json')
        );

        //Test field payee max 14
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_with_invalid_fields_error_6.json')
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_with_invalid_fields_error_6.json')
        );

        //Test field payee and payer are different
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_with_invalid_fields_error_7.json')
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_with_invalid_fields_error_7.json')
        );
    }

    public function test_create_transactions_payer_not_found_error()
    {
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_payer_not_found_error.json')
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_payer_not_found_error.json')
        );
    }

    public function test_create_transactions_payee_not_found_error()
    {
        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_payee_not_found_error.json')
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_payee_not_found_error.json')
        );
    }

    public function test_create_transactions_payer_as_store_error()
    {
        $this->seed();

        $response = $this->postJson(
            '/api/v1/transactions', 
            $this->getJsonRequest('test_create_transactions_payer_as_store_error.json')
        );

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertExactJson(
            $this->getJsonResponse('test_create_transactions_payer_as_store_error.json')
        );
    }
}
