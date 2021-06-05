<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Client::create([
            'user_id'           =>  7,
            'name'              =>  'Primer Cliente',
            'surname'           =>  'Wolof',
        ]);

        Client::create([
            'user_id'           =>  8,
            'name'              =>  'Segundo Cliente',
            'surname'           =>  'Wolof',
        ]);

    }
}
