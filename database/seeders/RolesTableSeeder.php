<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Role Types
         *
         */
        $RoleItems = [
            [
                'name'        => 'CEO',
                'slug'        => 'ceo',
                'description' => 'Se refiere al Presidente de Gabu',
                'level'       => 10,
            ],
            [
                'name'        => 'CTO',
                'slug'        => 'cto',
                'description' => 'Se refiere al Gerente de Tecnología de Gabu. Es lo más parecido a un súper usuario, esto porque pudiera ejecutar rutinas propias de tecnología',
                'level'       => 10,
            ],
            [
                'name'        => "Gabu's Employee",
                'slug'        => 'gabu.employee',
                'description' => 'Se refiere a cualquier Empleado de Gabu',
                'level'       => 7,
            ],
            [
                'name'        => 'Commerce Owner',
                'slug'        => 'commerce.owner',
                'description' => 'Se refiere al Dueño de la Cuenta, posiblemente el dueño del Comercio',
                'level'       => 6,
            ],
            [
                'name'        => 'Commerce Employee',
                'slug'        => 'commerce.employee',
                'description' => 'Son los Empleados de los Comercios',
                'level'       => 4,
            ],
            [
                'name'        => 'Client',
                'slug'        => 'client',
                'description' => 'Son los Clientes de Gabu',
                'level'       => 3,
            ],
            [
                'name'        => 'Unverified',
                'slug'        => 'unverified',
                'description' => 'Son aquellos visitantes de Gabu, por ende estos no tienen un rol en específico asociado',
                'level'       => 0,
            ],
        ];

        /*
         * Add Role Items
         *
         */
        foreach ($RoleItems as $RoleItem) {
            $newRoleItem = config('roles.models.role')::where('slug', '=', $RoleItem['slug'])->first();
            if ($newRoleItem === null) {
                $newRoleItem = config('roles.models.role')::create([
                    'name'          => $RoleItem['name'],
                    'slug'          => $RoleItem['slug'],
                    'description'   => $RoleItem['description'],
                    'level'         => $RoleItem['level'],
                ]);
            }
        }
    }
}
