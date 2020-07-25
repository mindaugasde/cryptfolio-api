<?php
declare(strict_types=1);

use Laravel\Lumen\Testing\DatabaseMigrations;

class AssetControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function login(): string
    {
        $user = factory('App\User')->create();
        $loginResponse = $this->actingAs($user)->json('POST', '/auth/login', [
            'email' => $user->email,
            'password' => 'aB123Cd',
        ]);
        return $loginResponse->response->getOriginalContent()['access_token'];
    }

    /** @test */
    public function logged_user_can_access_his_assets_endpoint(): void
    {
        factory('App\Asset')->create();
        $token = $this->login();
        $response = $this->json('GET', '/v1/asset', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response->assertResponseStatus(200);
        $this->assertJson($response->response->content());
        $this->seeJsonContains(['message' => '1 assets in total']);
    }

    /** @test */
    public function unauthorized_user_cant_access_assets_endpoint(): void
    {
        $response = $this->json('GET', '/v1/asset');
        $response->assertResponseStatus(401);
    }

    /** !TODO Next tests. Focusing to finish the task. */
}
