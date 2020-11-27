<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            ['language_code' => 'es_ES', 'language_name' => 'Español'],
            ['language_code' => 'en_GB', 'language_name' => 'English'],
        ];

        DB::table('languages')->insert($languages);
    }
}
