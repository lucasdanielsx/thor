<?php

namespace Tests\Feature\App\Http\Controllers;;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Tests\BaseTest;

class WalletControllerTest extends BaseTest
{
    public function test_find_by_document_success()
    {
        $this->seed();
        $user = $this->getCostumerUser();

        $response = $this->get('/api/v1/wallets/' . $user->document);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertExactJson(
          $this->getJsonResponse('test_find_by_document_success.json')
        );
    }

    public function test_find_by_document_error()
    {
        $this->seed();

        $response = $this->get('/api/v1/wallets/' . Str::uuid());

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
