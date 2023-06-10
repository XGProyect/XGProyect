<?php

namespace Database\Seeders;

use App\Models\Languages;
use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    public function run()
    {
        $languages = [
            ['name' => 'Español', 'code' => 'es'],
            ['name' => 'English', 'code' => 'en'],
        ];

        Languages::insert($languages);
    }
}
