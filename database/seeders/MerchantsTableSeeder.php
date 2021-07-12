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
            'commerce_id'           =>  1,
            'name'              =>  'Dueño Primer',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24),
        ]);

        Merchant::create([
            'user_id'           =>  6,
            'commerce_id'           =>  1,
            'name'              =>  'Empleado Primer',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24)
        ]);

        Merchant::create([
            'user_id'           =>  9,
            'commerce_id'           =>  2,
            'name'              =>  'Dueño Segundo',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24)
        ]);

        Merchant::create([
            'user_id'           =>  10,
            'commerce_id'           =>  2,
            'name'              =>  'Empleado Segundo',
            'surname'           =>  'Comercio',
            'id_public'         =>  Str::random(24)
        ]);
    }
}
