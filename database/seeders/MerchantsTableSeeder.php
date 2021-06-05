<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;

class MerchantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Merchant::create([
            'user_id'           =>  5,
            'name'              =>  'Dueño',
            'surname'           =>  'Comercio',
        ]);

        Merchant::create([
            'user_id'           =>  6,
            'name'              =>  'Empleado',
            'surname'           =>  'Comercio',
        ]);
    }
}
