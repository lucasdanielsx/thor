<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class BaseTest extends TestCase
{
    use RefreshDatabase;

    public function getJsonResponse(string $fileName) {
        $path = storage_path() . '/json-tests/responses/' . $fileName;

        return json_decode(file_get_contents($path), true);
    }

    public function getJsonRequest(string $fileName) {
        $path = storage_path() . '/json-tests/requests/' . $fileName;

        return json_decode(file_get_contents($path), true);
    }
}