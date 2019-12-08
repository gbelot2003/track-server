<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        DB::table('model_has_permissions')->delete();
        DB::table('permissions')->delete();

        $permissions = [
            'ver usuarios', 'crear usuarios', 'editar usuarios', 'suspender usuarios',
            'ver roles', 'crear roles', 'editar roles', 'suspender roles',
            'ver tareas', 'crear tareas', 'editar tareas', 'borrar tareas',
            'ver cliente', 'editar clientes', 'crear clientes', 'borrar clientes'
        ];


        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }


    }
}
