<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $ceoRole = config('roles.models.role')::where('slug', '=', 'ceo')->first();
        $ctoRole = config('roles.models.role')::where('slug', '=', 'cto')->first();
        $gabuEmployeeRole = config('roles.models.role')::where('slug', '=', 'gabu.employee')->first();

        $commerceOwnerRole = config('roles.models.role')::where('slug', '=', 'commerce.owner')->first();
        $commerceEmployeeRole = config('roles.models.role')::where('slug', '=', 'commerce.employee')->first();

        $clientRole = config('roles.models.role')::where('slug', '=', 'client')->first();

        $permissions = config('roles.models.permission')::all();



        /*
         * Add Users
         *
         */

        // System
        if (config('roles.models.defaultUser')::where('email', '=', 'system@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'system@gabu.com',
                'password'      =>  bcrypt(Str::random(18)),
                'flag_login'    =>  false,
                'observation_flag_login'    =>  'El sistema no necesita autenticarse',
                'username'      =>  'gabu',
                'cellphone_number'  =>  '+503123456781'
            ]);

            $newUser->attachRole($gabuEmployeeRole);
            foreach ($permissions as $permission) {
                $newUser->attachPermission($permission);
            }
        }

        // CEO
        if (config('roles.models.defaultUser')::where('email', '=', 'jazmine@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'jazmine@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'jazmine',
                'cellphone_number'  =>  '+503123456782'
            ]);

            $newUser->attachRole($ceoRole);
            foreach ($permissions as $permission) {
                $newUser->attachPermission($permission);
            }
        }

        // CTO
        if (config('roles.models.defaultUser')::where('email', '=', 'gustavo@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'gustavo@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'gutoccs',
                'cellphone_number'  =>  '+503123456783'
            ]);

            $newUser->attachRole($ctoRole);
            foreach ($permissions as $permission) {
                $newUser->attachPermission($permission);
            }
        }

        // Gabu's Employee
        if (config('roles.models.defaultUser')::where('email', '=', 'employee@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'employee@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'employee',
                'cellphone_number'  =>  '+503123456784'
            ]);

            $newUser->attachRole($gabuEmployeeRole);
        }

        // First Commerce Owner
        if (config('roles.models.defaultUser')::where('email', '=', 'commerceowner@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'commerceowner@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'commerce_owner',
                'cellphone_number'  =>  '+503123456785'
            ]);

            $newUser->attachRole($commerceOwnerRole);
        }

        // First Commerce Employee
        if (config('roles.models.defaultUser')::where('email', '=', 'commerceemployee@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'commerceemployee@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'commerce_employee',
                'cellphone_number'  =>  '+503123456786'
            ]);

            $newUser->attachRole($commerceEmployeeRole);
        }

        // Client 1
        if (config('roles.models.defaultUser')::where('email', '=', 'client1@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'client1@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'client_1',
                'cellphone_number'  =>  '+503123456787'
            ]);

            $newUser->attachRole($clientRole);
        }

        // Client 2
        if (config('roles.models.defaultUser')::where('email', '=', 'client2@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'client2@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'client_2',
                'cellphone_number'  =>  '+503123456788'
            ]);

            $newUser->attachRole($clientRole);
        }

        // Second Commerce Owner
        if (config('roles.models.defaultUser')::where('email', '=', 'commerceowner2@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'commerceowner2@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'commerce_owner_2',
                'cellphone_number'  =>  '+503123456321'
            ]);

            $newUser->attachRole($commerceOwnerRole);
        }

        // Second Commerce Employee
        if (config('roles.models.defaultUser')::where('email', '=', 'commerceemployee2@gabu.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'commerceemployee2@gabu.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'commerce_employee_2',
                'cellphone_number'  =>  '+50312345123'
            ]);

            $newUser->attachRole($commerceEmployeeRole);
        }

    }
}
