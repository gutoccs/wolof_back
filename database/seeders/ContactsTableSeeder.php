<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Contact::create([
            'commerce_id'     =>  1,
        ]);

        Contact::create([
            'commerce_id'     =>  2,
        ]);
    }
}
