<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{

    use RefreshDatabase;

    private $firstUser;

    private $lastUser;

    private $itemUser;

    /**
     * setUp function
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        \Artisan::call('migrate', ['-vvv' => true]);

        Role::create(['name' => 'Administrador']);

        factory(User::class, 30)->create();

        factory(User::class, 1)->create(['name' => 'gerard', 'email' => 'anoy01@anon.com']);
        factory(User::class, 1)->create(['name' => 'vale', 'email' => 'anoy02@anon.com']);

        $this->firstUser = User::findOrFail(1);
        $this->firstUser->assignRole('Administrador');

        $this->lastUser = User::findOrFail(30);

        $this->itemUser = User::findOrFail(31);
    }

    /**
     * Usuarios sin permisos no pueden ingresar a
     * este endpoint
     *
     * @return void
     */
    /** @test */
    public function no_user_can_enter_index_without_permissions()
    {
        Passport::actingAs($this->lastUser);

        $name = $this->itemUser->name;

        // Primer parametro de nombre
        $response = $this->json('GET', "api/v1/users");

        $response->assertStatus(403);
    }

    /**
     * Usuarios invitados no pueden ingresar a este endpoint
     *
     * @return void
     */
    /** @test */
    public function no_guess_can_enter_index_without_permissions()
    {
        $name = $this->itemUser->name;

        // Primer parametro de nombre
        $response = $this->json('GET', "api/v1/users");

        $response->assertStatus(401);
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
        Passport::actingAs($this->firstUser);

        $name = $this->itemUser->name;

        // Primer parametro de nombre
        $response = $this->json('GET', "api/v1/users?name=$name");

        $response->assertStatus(200);

        $response->assertSee("gerar");

        $responseArray = json_decode($response->getContent());

        $this->assertEquals(1, $responseArray->total);
    }

    /** @test */
    public function index_filter_by_email()
    {
        Passport::actingAs($this->firstUser);

        // Buscamos todas las coincidencias, el resultado es 2
        $response = $this->json('get', "api/v1/users?email=anon.com");

        $response->assertStatus(200);

        $response->assertSee($this->itemUser->email);

        $responseArray = json_decode($response->getContent());

        $this->assertEquals(2, $responseArray->total);
    }
}
