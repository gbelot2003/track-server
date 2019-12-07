<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

class UserControllerTest extends TestCase
{

    use RefreshDatabase;

    public $firstUser;

    public $lastUser;

    /**
     * setUp function
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        \Artisan::call('migrate', ['-vvv' => true]);
        factory(User::class, 30)->create();
        factory(User::class, 1)->create(['name' => 'gerard']);
        $this->firstUser = User::findOrFail(1);
        $this->lastUser = User::findOrFail(30);
    }

    /** @test */
    public function get_paginated_list_of_users()
    {
        Passport::actingAs($this->firstUser);

        $response = $this->json('get', 'api/v1/users');

        // No miramos al primer usuario por la paginacion
        $response->assertDontSee($this->firstUser->name);

        // Miramos al ultimo usuario
        $response->assertSee($this->lastUser->name);

        // nos envia parte de la paginacion
        $response->assertSeeText('current_page');
    }

    /** @test */
    public function index_filter_by_any_given_parameter()
    {
        Passport::actingAs($this->lastUser);

        // Primer parametro de nombre
        $response = $this->json('get', 'api/v1/users?name=gera');

        $response->assertSee("gerar");
        $response->assertJsonCount(1);

    }


}
