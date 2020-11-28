<?php

namespace Database\Seeders;

use Database\Seeders\ChangelogSeeder;
use Database\Seeders\LanguagesSeeder;
use Database\Seeders\OptionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(ChangelogSeeder::class);
        $this->call(LanguagesSeeder::class);
        $this->call(OptionsSeeder::class);
        Model::reguard();
    }
}
