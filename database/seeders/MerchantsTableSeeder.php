<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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
            'name'              =>  'Dueño Primer',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24)
        ]);

        Merchant::create([
            'user_id'           =>  6,
            'name'              =>  'Empleado Primer',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24)
        ]);

        Merchant::create([
            'user_id'           =>  9,
            'name'              =>  'Dueño Segundo',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24)
        ]);

        Merchant::create([
            'user_id'           =>  10,
            'name'              =>  'Empleado Segundo',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24)
        ]);
    }
}
