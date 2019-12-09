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

        // Migramos la base de datos
        \Artisan::call('migrate', ['-vvv' => true]);

        // Creamos el rol de administrador
        Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Editor']);


        // Creamos 30 usuarios
        factory(User::class, 30)->create()->each(function($user){
            $user->assignRole('Editor');
        });

        // Creamos a usuarios de pruebas
        factory(User::class, 1)->create(
            ['name' => 'gerard', 'email' => 'anoy01@anon.com']);

        factory(User::class, 1)->create(
            ['name' => 'vale', 'email' => 'anoy02@anon.com', 'status' => false]);

        // Asignamos usuarios a variables globales
        $this->firstUser = User::findOrFail(1);
        $this->firstUser->assignRole('Administrador');

        $this->lastUser = User::findOrFail(30);
        $this->lastUser->assignRole('Editor');

        $this->itemUser = User::findOrFail(31);
        $this->itemUser->assignRole('Editor');
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
        //$this->withExceptionHandling();

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
    public function index_filter_by_nane()
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

    /** @test */
    public function index_filter_by_status()
    {
        Passport::actingAs($this->firstUser);

        // Buscamos todas las coincidencias, el resultado es 1
        $response = $this->json('get', "api/v1/users?status");

        $response->assertStatus(200);

        // Solo este usuario tiene el status false
        $response->assertSee('anoy02@anon.com');

        $responseArray = json_decode($response->getContent());

        $this->assertEquals(1, $responseArray->total);
    }

    /** we need to get a users info form show method */

    /** @test */
    public function user_with_permissions_get_info_from_show_method()
    {
        $this->withExceptionHandling();

        Passport::actingAs($this->firstUser);

        // Buscamos al ultimo usuario de la lista No 31
        $response = $this->json('get', "api/v1/users/" . $this->itemUser->id);

        $response->assertStatus(200);

        $response->assertSee($this->itemUser->email);

        // check if users has some role
        $response->assertSee("Editor");
    }

    /** @test */
    public function user_with_no_autorization_cant_get_info()
    {
        Passport::actingAs($this->lastUser);

        // Buscamos al ultimo usuario de la lista No 31
        $response = $this->json('get', "api/v1/users/" . $this->itemUser->id);

        // Sin acceso autorizado
        $response->assertStatus(403);
    }

    /** @test */
    public function guess_cant_access_to_authorization_area()
    {
        $this->withExceptionHandling();

        $response = $this->json('get', "api/v1/users/" . $this->itemUser->id);

        // Sin acceso autorizado
        $response->assertStatus(401);
    }

    /** We need to update the informactio of users in Update Method */

    /** @test */
    public function authorize_user_can_change_name_of_an_item()
    {
        Passport::actingAs($this->firstUser);

        $data = array(
            'name' => 'luisAndress',
            'email' => $this->itemUser->email,
        );

        $response = $this->json('PUT', "api/v1/users/" . $this->itemUser->id, $data);

        $response->assertStatus(200);

        $response->assertSee('luisAndress');
    }

    /** @test */
    public function authorize_user_can_change_email_of_an_item()
    {
        Passport::actingAs($this->firstUser);

        $data = array(
            'name' => $this->itemUser->name,
            'email' => "anon@anona.come",
        );

        $response = $this->json('PUT', "api/v1/users/" . $this->itemUser->id, $data);

        $response->assertStatus(200);

        $response->assertSee('anon@anona.come');
    }

    /** @test */
    public function an_unauthorize_user_cant_change_items()
    {
        Passport::actingAs($this->lastUser);

        $data = array(
            'name' => $this->itemUser->name,
            'email' => "anon@anona.come",
        );

        $response = $this->json('PUT', "api/v1/users/" . $this->itemUser->id, $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_guess_cant_change_items()
    {
        $data = array(
            'name' => $this->itemUser->name,
            'email' => "anon@anona.come",
        );

        $response = $this->json('PUT', "api/v1/users/" . $this->itemUser->id, $data);

        $response->assertStatus(401);
    }

}
