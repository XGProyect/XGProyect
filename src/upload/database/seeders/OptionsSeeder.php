<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OptionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $options = [
            ['option_name' => 'game_name', 'option_value' => 'XG Proyect'],
            ['option_name' => 'game_logo', 'option_value' => 'https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-white.png'],
            ['option_name' => 'lang', 'option_value' => 'spanish'],
            ['option_name' => 'game_speed', 'option_value' => '2500'],
            ['option_name' => 'fleet_speed', 'option_value' => '2500'],
            ['option_name' => 'resource_multiplier', 'option_value' => '1'],
            ['option_name' => 'admin_email', 'option_value' => ''],
            ['option_name' => 'forum_url', 'option_value' => 'https://www.xgproyect.org/'],
            ['option_name' => 'game_enable', 'option_value' => '1'],
            ['option_name' => 'close_reason', 'option_value' => 'Sorry, the server is currently offline.'],
            ['option_name' => 'date_time_zone', 'option_value' => 'America/Argentina/Buenos_Aires'],
            ['option_name' => 'date_format', 'option_value' => 'd.m.Y'],
            ['option_name' => 'date_format_extended', 'option_value' => 'd.m.Y H:i:s'],
            ['option_name' => 'adm_attack', 'option_value' => '1'],
            ['option_name' => 'fleet_cdr', 'option_value' => '30'],
            ['option_name' => 'defs_cdr', 'option_value' => '30'],
            ['option_name' => 'noobprotection', 'option_value' => '1'],
            ['option_name' => 'noobprotectiontime', 'option_value' => '50000'],
            ['option_name' => 'noobprotectionmulti', 'option_value' => '5'],
            ['option_name' => 'modules', 'option_value' => '1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;0;1;1'],
            ['option_name' => 'admin_permissions', 'option_value' => '{\"server\":{\"1\":0,\"2\":0,\"3\":1},\"modules\":{\"1\":0,\"2\":0,\"3\":1},\"planets\":{\"1\":0,\"2\":1,\"3\":1},\"registration\":{\"1\":0,\"2\":1,\"3\":1},\"statistics\":{\"1\":0,\"2\":1,\"3\":1},\"premium\":{\"1\":0,\"2\":0,\"3\":1},\"tasks\":{\"1\":0,\"2\":0,\"3\":1},\"errors\":{\"1\":0,\"2\":0,\"3\":1},\"fleets\":{\"1\":1,\"2\":1,\"3\":1},\"messages\":{\"1\":1,\"2\":1,\"3\":1},\"maker\":{\"1\":0,\"2\":1,\"3\":1},\"users\":{\"1\":1,\"2\":1,\"3\":1},\"alliances\":{\"1\":1,\"2\":1,\"3\":1},\"languages\":{\"1\":0,\"2\":0,\"3\":1},\"changelog\":{\"1\":0,\"2\":0,\"3\":1},\"permissions\":{\"1\":0,\"2\":0,\"3\":1},\"backup\":{\"1\":0,\"2\":1,\"3\":1},\"encrypter\":{\"1\":1,\"2\":1,\"3\":1},\"announcement\":{\"1\":0,\"2\":1,\"3\":1},\"ban\":{\"1\":1,\"2\":1,\"3\":1},\"rebuildhighscores\":{\"1\":0,\"2\":1,\"3\":1},\"update\":{\"1\":0,\"2\":0,\"3\":1},\"migrate\":{\"1\":0,\"2\":0,\"3\":1},\"repair\":{\"1\":0,\"2\":0,\"3\":1},\"reset\":{\"1\":0,\"2\":0,\"3\":1}}'],
            ['option_name' => 'initial_fields', 'option_value' => '163'],
            ['option_name' => 'metal_basic_income', 'option_value' => '90'],
            ['option_name' => 'crystal_basic_income', 'option_value' => '45'],
            ['option_name' => 'deuterium_basic_income', 'option_value' => '0'],
            ['option_name' => 'energy_basic_income', 'option_value' => '0'],
            ['option_name' => 'reg_enable', 'option_value' => '1'],
            ['option_name' => 'reg_welcome_message', 'option_value' => '1'],
            ['option_name' => 'reg_welcome_email', 'option_value' => '1'],
            ['option_name' => 'stat_points', 'option_value' => '1000'],
            ['option_name' => 'stat_update_time', 'option_value' => '1'],
            ['option_name' => 'stat_admin_level', 'option_value' => '0'],
            ['option_name' => 'stat_last_update', 'option_value' => '0'],
            ['option_name' => 'premium_url', 'option_value' => 'https://www.xgproyect.org/game/index.php?page=officier'],
            ['option_name' => 'merchant_price', 'option_value' => '3500'],
            ['option_name' => 'auto_backup', 'option_value' => '0'],
            ['option_name' => 'last_backup', 'option_value' => '0'],
            ['option_name' => 'last_cleanup', 'option_value' => '0'],
            ['option_name' => 'version', 'option_value' => Config::get('constants.system.version')],
            ['option_name' => 'lastsettedgalaxypos', 'option_value' => '1'],
            ['option_name' => 'lastsettedsystempos', 'option_value' => '1'],
            ['option_name' => 'lastsettedplanetpos', 'option_value' => '1'],
            ['option_name' => 'merchant_base_min_exchange_rate', 'option_value' => '0.7'],
            ['option_name' => 'merchant_base_max_exchange_rate', 'option_value' => '1'],
            ['option_name' => 'merchant_metal_multiplier', 'option_value' => '3'],
            ['option_name' => 'merchant_crystal_multiplier', 'option_value' => '2'],
            ['option_name' => 'merchant_deuterium_multiplier', 'option_value' => '1'],
            ['option_name' => 'registration_dark_matter', 'option_value' => '0'],
            ['option_name' => 'mailing_protocol', 'option_value' => 'mail'],
            ['option_name' => 'mailing_smtp_host', 'option_value' => ''],
            ['option_name' => 'mailing_smtp_user', 'option_value' => ''],
            ['option_name' => 'mailing_smtp_pass', 'option_value' => ''],
            ['option_name' => 'mailing_smtp_port', 'option_value' => '25'],
            ['option_name' => 'mailing_smtp_timeout', 'option_value' => '5'],
            ['option_name' => 'mailing_smtp_crypto', 'option_value' => ''],
        ];

        DB::table('options')->insert($options);
    }
}
