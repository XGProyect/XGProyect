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
        $changelog = [
            ['changelog_id' => '1', 'changelog_lang_id' => '1', 'changelog_version' => '4.0.0', 'changelog_date' => '1989-02-19', 'changelog_description' => '- Ejemplo 1'],
            ['changelog_id' => '2', 'changelog_lang_id' => '1', 'changelog_version' => '4.1.0', 'changelog_date' => '2008-01-01', 'changelog_description' => '- Ejemplo 2'],
            ['changelog_id' => '3', 'changelog_lang_id' => '1', 'changelog_version' => '4.2.0', 'changelog_date' => '2020-11-01', 'changelog_description' => '- Ejemplo 3'],
            ['changelog_id' => '4', 'changelog_lang_id' => '2', 'changelog_version' => '4.0.0', 'changelog_date' => '1989-02-19', 'changelog_description' => '- Example 1'],
            ['changelog_id' => '5', 'changelog_lang_id' => '2', 'changelog_version' => '4.1.0', 'changelog_date' => '2008-01-01', 'changelog_description' => '- Example 2'],
            ['changelog_id' => '6', 'changelog_lang_id' => '2', 'changelog_version' => '4.2.0', 'changelog_date' => '2020-11-01', 'changelog_description' => '- Example 3'],
        ];

        DB::table('changelog')->insert($changelog);
    }
}
