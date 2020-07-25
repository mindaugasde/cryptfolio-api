<?php
declare(strict_types=1);

use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function login(): AuthControllerTest
    {
        $user = factory('App\User')->create();
        return $this->actingAs($user)->json('POST', '/auth/login', [
            'email' => $user->email,
            'password' => 'aB123Cd',
        ]);
    }

    /** @test */
    public function login_endpoint_is_working(): void
    {
        $response = $this->login();
        $response->assertResponseStatus(200);
    }

    /** @test */
    public function customer_cant_login_using_wrong_credentials(): void
    {
        $response = $this->json('POST', '/auth/login', [
            'email' => 'tester@test.com',
            'password' => '123asd',
        ]);
        $response->assertResponseStatus(404);
    }

    /** @test */
    public function refresh_token_endpoint_is_working(): void
    {
        $loginResponse = $this->login();
        $token = $loginResponse->response->getOriginalContent()['access_token'];
        $refreshResponse = $this->json('GET', '/auth/refresh', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $refreshResponse->assertResponseStatus(200);
    }

    /** @test */
    public function refresh_token_endpoint_shouldnt_work_when_authorization_header_is_not_provided(): void
    {
        $refreshResponse = $this->json('GET', '/auth/refresh', [], []);
        $refreshResponse->assertResponseStatus(401);
    }
}
