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
        $wolofEmployeeRole = config('roles.models.role')::where('slug', '=', 'wolof.employee')->first();

        $commerceOwnerRole = config('roles.models.role')::where('slug', '=', 'commerce.owner')->first();
        $commerceEmployeeRole = config('roles.models.role')::where('slug', '=', 'commerce.employee')->first();

        $clientRole = config('roles.models.role')::where('slug', '=', 'client')->first();

        $permissions = config('roles.models.permission')::all();



        /*
         * Add Users
         *
         */

        // System
        if (config('roles.models.defaultUser')::where('email', '=', 'system@wolof.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'system@wolof.com',
                'password'      =>  bcrypt(Str::random(18)),
                'flag_login'    =>  false,
                'observation_flag_login'    =>  'El sistema no necesita autenticarse'
            ]);

            $newUser->attachRole($wolofEmployeeRole);
            foreach ($permissions as $permission) {
                $newUser->attachPermission($permission);
            }
        }

        // CEO
        if (config('roles.models.defaultUser')::where('email', '=', 'jazmin@wolof.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'jazmin@wolof.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'jazmin'
            ]);

            $newUser->attachRole($ceoRole);
            foreach ($permissions as $permission) {
                $newUser->attachPermission($permission);
            }
        }

        // CTO
        if (config('roles.models.defaultUser')::where('email', '=', 'gustavo@wolof.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'gustavo@wolof.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null,
                'username'      =>  'gutoccs'
            ]);

            $newUser->attachRole($ctoRole);
            foreach ($permissions as $permission) {
                $newUser->attachPermission($permission);
            }
        }

        // Wolof's Employee
        if (config('roles.models.defaultUser')::where('email', '=', 'employee@wolof.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'employee@wolof.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null
            ]);

            $newUser->attachRole($wolofEmployeeRole);
        }

        // Commerce Owner
        if (config('roles.models.defaultUser')::where('email', '=', 'commerceowner@wolof.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'commerceowner@wolof.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null
            ]);

            $newUser->attachRole($commerceOwnerRole);
        }

        // Commerce Employee
        if (config('roles.models.defaultUser')::where('email', '=', 'commerceemployee@wolof.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'commerceemployee@wolof.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null
            ]);

            $newUser->attachRole($commerceEmployeeRole);
        }

        // Client
        if (config('roles.models.defaultUser')::where('email', '=', 'client@wolof.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'email'         => 'client@wolof.com',
                'password'      =>  bcrypt('password'),
                'flag_login'    =>  true,
                'observation_flag_login'    =>  null
            ]);

            $newUser->attachRole($clientRole);
        }

    }
}
