<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
        ]);

        Employee::create([
            'user_id'           =>  2,
            'full_name'         =>  'Gabriela Díaz',
        ]);

        Employee::create([
            'user_id'           =>  3,
            'full_name'         =>  'Gustavo Escobar',
        ]);

        Employee::create([
            'user_id'           =>  4,
            'full_name'         =>  'Empleado Wolof',
        ]);
    }
}
