<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        DB::table('role_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('roles')->delete();

        $roles = [
            'administrador',
            'Conserje',
            'recepcionista',
        ];

        $permissionsAdmin = [
            'ver usuarios', 'crear usuarios', 'editar usuarios', 'suspender usuarios',
            'ver roles', 'crear roles', 'editar roles', 'suspender roles',
            'ver tareas', 'crear tareas', 'editar tareas', 'borrar tareas',
            'ver cliente', 'editar clientes', 'crear clientes', 'borrar clientes'
        ];

        foreach ($roles as $roles) {
            Role::create(['name' => $roles]);
        }

        $role = Role::findByName('administrador');
        $role->givePermissionTo($permissionsAdmin);



    }
}
