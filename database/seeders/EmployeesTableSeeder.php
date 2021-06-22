<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Employee;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Employee::create([
            'user_id'           =>  1,
            'full_name'         =>  'System Wolof',
            'id_public'         =>  Str::random(24)
        ]);

        Employee::create([
            'user_id'           =>  2,
            'full_name'         =>  'Gabriela Díaz',
            'id_public'         =>   Str::random(24)
        ]);

        Employee::create([
            'user_id'           =>  3,
            'full_name'         =>  'Gustavo Escobar',
            'id_public'         =>   Str::random(24)
        ]);

        Employee::create([
            'user_id'           =>  4,
            'full_name'         =>  'Empleado Wolof',
            'id_public'         =>   Str::random(24)
        ]);
    }
}
