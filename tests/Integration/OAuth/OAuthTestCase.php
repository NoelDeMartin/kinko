<?php

namespace Tests\Integration\OAuth;

use Tests\TestCase;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\TestResponse;

abstract class OAuthTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        TestResponse::macro('assertOAuthError', function ($error = '*', $message = null) {
            $this->assertStatus(Response::HTTP_BAD_REQUEST);
            $this->assertJsonStructure(['error']);
            PHPUnit::assertTrue(str_is($error, $this->json('error')));

            if (!is_null($message)) {
                $this->assertJsonStructure(['error_description']);
                PHPUnit::assertTrue(str_is($message, $this->json('error_description')));
            }

            return $this;
        });
    }
}
