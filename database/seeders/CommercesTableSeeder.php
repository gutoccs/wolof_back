<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Commerce;

class CommercesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Commerce::create([
            'id_public'     =>  Str::random(24),
            'trade_name'    =>  'Pida Pollo',
            'slogan'        =>  'No vueles, mejor come Pollo',
        ]);

        Commerce::create([
            'id_public'     =>  Str::random(24),
            'trade_name'    =>  'Porky',
            'slogan'        =>  'Un sabor diferente',
        ]);

    }
}
