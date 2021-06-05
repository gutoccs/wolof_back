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
            'cellphone_number'  =>  '+503123456781'
        ]);

        Employee::create([
            'user_id'           =>  2,
            'full_name'         =>  'Gabriela Díaz',
            'cellphone_number'  =>  '+503123456782'
        ]);

        Employee::create([
            'user_id'           =>  3,
            'full_name'         =>  'Gustavo Escobar',
            'cellphone_number'  =>  '+58123456783'
        ]);

        Employee::create([
            'user_id'           =>  4,
            'full_name'         =>  'Empleado Wolof',
            'cellphone_number'  =>  '+503123456784'
        ]);
    }
}
