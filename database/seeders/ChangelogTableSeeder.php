<?php

namespace Database\Seeders;

use App\Models\Changelog;
use Illuminate\Database\Seeder;

class ChangelogTableSeeder extends Seeder
{
    public function run()
    {
        $changelog = [
            ['changelog_lang_id' => 1, 'changelog_version' => '3.0.0', 'changelog_date' => '2013-05-13', 'changelog_description' => '- Ejemplo 1'],
            ['changelog_lang_id' => 1, 'changelog_version' => '3.1.0', 'changelog_date' => '2013-06-13', 'changelog_description' => '- Ejemplo 2'],
            ['changelog_lang_id' => 1, 'changelog_version' => '3.2.0', 'changelog_date' => '2013-11-08', 'changelog_description' => '- Ejemplo 3'],
            ['changelog_lang_id' => 2, 'changelog_version' => '3.0.0', 'changelog_date' => '2013-05-13', 'changelog_description' => '- Example 1'],
            ['changelog_lang_id' => 2, 'changelog_version' => '3.1.0', 'changelog_date' => '2013-06-13', 'changelog_description' => '- Example 2'],
            ['changelog_lang_id' => 2, 'changelog_version' => '3.2.0', 'changelog_date' => '2013-11-08', 'changelog_description' => '- Example 3'],
        ];

        Changelog::insert($changelog);
    }
}
