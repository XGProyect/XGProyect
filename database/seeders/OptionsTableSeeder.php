<?php

namespace Database\Seeders;

use App\Models\Options;
use Illuminate\Database\Seeder;

class OptionsTableSeeder extends Seeder
{
    public function run()
    {
        $options = [
            ['name' => 'game_name', 'value' => 'XG Proyect', 'type' => 'string'],
            ['name' => 'game_logo', 'value' => 'https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-white.png', 'type' => 'string'],
            ['name' => 'lang', 'value' => 'spanish', 'type' => 'string'],
            ['name' => 'game_speed', 'value' => '2500', 'type' => 'int'],
            ['name' => 'fleet_speed', 'value' => '2500', 'type' => 'int'],
            ['name' => 'resource_multiplier', 'value' => '1', 'type' => 'int'],
            ['name' => 'admin_email', 'value' => '', 'type' => 'string'],
            ['name' => 'forum_url', 'value' => 'https://www.xgproyect.org/', 'type' => 'string'],
            ['name' => 'game_enable', 'value' => '1', 'type' => 'int'],
            ['name' => 'close_reason', 'value' => 'Sorry, the server is currently offline.', 'type' => 'string'],
            ['name' => 'date_time_zone', 'value' => 'America/Argentina/Buenos_Aires', 'type' => 'string'],
            ['name' => 'date_format', 'value' => 'd.m.Y', 'type' => 'string'],
            ['name' => 'date_format_extended', 'value' => 'd.m.Y H:i:s', 'type' => 'string'],
            ['name' => 'adm_attack', 'value' => '1', 'type' => 'int'],
            ['name' => 'fleet_cdr', 'value' => '30', 'type' => 'int'],
            ['name' => 'defs_cdr', 'value' => '30', 'type' => 'int'],
            ['name' => 'noobprotection', 'value' => '1', 'type' => 'int'],
            ['name' => 'noobprotectiontime', 'value' => '50000', 'type' => 'int'],
            ['name' => 'noobprotectionmulti', 'value' => '5', 'type' => 'int'],
            ['name' => 'modules', 'value' => '1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;0;1;1', 'type' => 'string'],
            ['name' => 'admin_permissions', 'value' => '{"server":{"1":0,"2":0,"3":1},"modules":{"1":0,"2":0,"3":1},"planets":{"1":0,"2":1,"3":1},"registration":{"1":0,"2":1,"3":1},"statistics":{"1":0,"2":1,"3":1},"premium":{"1":0,"2":0,"3":1},"tasks":{"1":0,"2":0,"3":1},"errors":{"1":0,"2":0,"3":1},"fleets":{"1":1,"2":1,"3":1},"messages":{"1":1,"2":1,"3":1},"maker":{"1":0,"2":1,"3":1},"users":{"1":1,"2":1,"3":1},"alliances":{"1":1,"2":1,"3":1},"languages":{"1":0,"2":0,"3":1},"changelog":{"1":0,"2":0,"3":1},"permissions":{"1":0,"2":0,"3":1},"backup":{"1":0,"2":1,"3":1},"encrypter":{"1":1,"2":1,"3":1},"announcement":{"1":0,"2":1,"3":1},"ban":{"1":1,"2":1,"3":1},"rebuildhighscores":{"1":0,"2":1,"3":1},"update":{"1":0,"2":0,"3":1},"repair":{"1":0,"2":0,"3":1},"reset":{"1":0,"2":0,"3":1}}', 'type' => 'string'],
            ['name' => 'initial_fields', 'value' => '163', 'type' => 'int'],
            ['name' => 'metal_basic_income', 'value' => '90', 'type' => 'int'],
            ['name' => 'crystal_basic_income', 'value' => '45', 'type' => 'int'],
            ['name' => 'deuterium_basic_income', 'value' => '0', 'type' => 'int'],
            ['name' => 'energy_basic_income', 'value' => '0', 'type' => 'int'],
            ['name' => 'reg_enable', 'value' => '1', 'type' => 'int'],
            ['name' => 'reg_welcome_message', 'value' => '1', 'type' => 'int'],
            ['name' => 'reg_welcome_email', 'value' => '1', 'type' => 'int'],
            ['name' => 'stat_points', 'value' => '1000', 'type' => 'int'],
            ['name' => 'stat_update_time', 'value' => '1', 'type' => 'int'],
            ['name' => 'stat_admin_level', 'value' => '0', 'type' => 'int'],
            ['name' => 'stat_last_update', 'value' => '0', 'type' => 'int'],
            ['name' => 'premium_url', 'value' => 'https://www.xgproyect.org/game.php?page=premium', 'type' => 'string'],
            ['name' => 'merchant_price', 'value' => '3500', 'type' => 'int'],
            ['name' => 'auto_backup', 'value' => '0', 'type' => 'int'],
            ['name' => 'last_backup', 'value' => '0', 'type' => 'int'],
            ['name' => 'last_cleanup', 'value' => '0', 'type' => 'int'],
            ['name' => 'version', 'value' => config('version.files'), 'type' => 'string'],
            ['name' => 'lastsettedgalaxypos', 'value' => '1', 'type' => 'int'],
            ['name' => 'lastsettedsystempos', 'value' => '1', 'type' => 'int'],
            ['name' => 'lastsettedplanetpos', 'value' => '1', 'type' => 'int'],
            ['name' => 'merchant_base_min_exchange_rate', 'value' => '0.7', 'type' => 'float'],
            ['name' => 'merchant_base_max_exchange_rate', 'value' => '1', 'type' => 'float'],
            ['name' => 'merchant_metal_multiplier', 'value' => '3', 'type' => 'int'],
            ['name' => 'merchant_crystal_multiplier', 'value' => '2', 'type' => 'int'],
            ['name' => 'merchant_deuterium_multiplier', 'value' => '1', 'type' => 'int'],
            ['name' => 'registration_dark_matter', 'value' => '0', 'type' => 'int'],
            ['name' => 'mailing_protocol', 'value' => 'smtp', 'type' => 'string'],
            ['name' => 'mailing_smtp_host', 'value' => 'mailpit', 'type' => 'string'],
            ['name' => 'mailing_smtp_user', 'value' => '', 'type' => 'string'],
            ['name' => 'mailing_smtp_pass', 'value' => '', 'type' => 'string'],
            ['name' => 'mailing_smtp_port', 'value' => '1025', 'type' => 'int'],
            ['name' => 'mailing_smtp_timeout', 'value' => '5', 'type' => 'int'],
            ['name' => 'mailing_smtp_crypto', 'value' => '', 'type' => 'string'],
        ];

        Options::insert($options);
    }
}
