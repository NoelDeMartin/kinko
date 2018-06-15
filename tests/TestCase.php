<?php

namespace Tests;

use Kinko\Models\User;
use Laravel\Passport\Passport;
use Tests\Concerns\CreatesApplication;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use WithFaker;
    use RefreshDatabase;
    use CreatesApplication;

    protected function setUp()
    {
        parent::setUp();

        $this->setUpFaker();
    }

    public function login($user = null)
    {
        return $this->actingAs($user ?: factory(User::class)->create());
    }

    /**
     * Set the currently logged in user for the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $driver
     * @return $this
     */
    public function actingAs(Authenticatable $user, $driver = null)
    {
        Passport::actingAs($user);

        return parent::be($user, $driver);
    }
}
