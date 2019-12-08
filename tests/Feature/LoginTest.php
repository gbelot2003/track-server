<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        \Artisan::call('passport:install', ['-vvv' => true]);
    }

    /** @test */
    public function oauth_actions_test()
    {
        factory(User::class, 1)->create(['id' => 1]);
        $oauth_client = DB::table('oauth_clients')
            ->where('id', 2)->first();//OauthClients::findOrFail(2);
        $secret = $oauth_client->secret;
        $user = User::findOrFail(1);
        $body = [
            'grant_type' => 'password',
            'client_id' => '2',
            'client_secret' => $secret,
            'username' => $user->email,
            'password' => 'password',
            'scope' => '*',
        ];
        $this->assertDatabaseHas('oauth_clients', ['secret' => $secret]);
        $this->assertDatabaseHas('users', ['email' => $user->email]);
        $this->json('POST', 'oauth/token', $body, ['Content-Type' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);
    }

    /** @test */
    public function login_action_test()
    {
        $user = factory(User::class, 1)->create();
        $body = [
            'email' => $user[0]->email,
            'password' => 'password'
        ];
        $this->json('POST', 'api/v1/login', $body)
            ->assertStatus(200);
    }

    /** @test */
    public function max_attemps_will_block_the_login_permanent()
    {
        $user = factory(User::class, 1)->create();
        $body = [
            'email' => $user[0]->email,
            'password' => 'Bad-Password'
        ];

        for($i = 1; $i < 5; $i++){
            $this->json('POST', 'api/v1/login', $body)
            ->assertStatus(401);
        }

        $this->json('POST', 'api/v1/login', $body)
            ->assertStatus(401);

    }

    /**
     * Esta prueba no esta terminada
     *
     * @return void
     */
    /** @test */
    public function a_deactivated_user_cant_login_test()
    {
        $user = factory(User::class, 1)
            ->create(['status' => false]);
        $body = [
            'email' => $user[0]->email,
            'password' => 'password'
        ];
        $this->json('POST', 'api/v1/login', $body)
            ->assertStatus(401);

    }

    /** @test */
    public function register_action_test()
    {
        $body = [
            'name' => 'Gerardo',
            'email' => 'gbelot@tester.com',
            'password' => 'password01',
        ];
        $this->json('POST', 'api/v1/register', $body, ['Accept' => 'application/json'])
            ->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'gbelot@tester.com']);
    }

    /** @test */
    public function user_logout_action()
    {
        Passport::actingAs(
            $user = factory(User::class)->create()
        );

        $response = $this->post('api/v1/logout', [])
            ->assertStatus(200);
    }

}
