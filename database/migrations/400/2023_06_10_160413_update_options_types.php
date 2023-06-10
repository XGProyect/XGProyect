<?php

use App\Models\Options;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Options::where(['name' => 'game_name'])->update(['type' => 'string']);
        Options::where(['name' => 'game_logo'])->update(['type' => 'string']);
        Options::where(['name' => 'lang'])->update(['type' => 'string']);
        Options::where(['name' => 'game_speed'])->update(['type' => 'int']);
        Options::where(['name' => 'fleet_speed'])->update(['type' => 'int']);
        Options::where(['name' => 'resource_multiplier'])->update(['type' => 'int']);
        Options::where(['name' => 'admin_email'])->update(['type' => 'string']);
        Options::where(['name' => 'forum_url'])->update(['type' => 'string']);
        Options::where(['name' => 'game_enable'])->update(['type' => 'int']);
        Options::where(['name' => 'close_reason'])->update(['type' => 'string']);
        Options::where(['name' => 'date_time_zone'])->update(['type' => 'string']);
        Options::where(['name' => 'date_format'])->update(['type' => 'string']);
        Options::where(['name' => 'date_format_extended'])->update(['type' => 'string']);
        Options::where(['name' => 'adm_attack'])->update(['type' => 'int']);
        Options::where(['name' => 'fleet_cdr'])->update(['type' => 'int']);
        Options::where(['name' => 'defs_cdr'])->update(['type' => 'int']);
        Options::where(['name' => 'noobprotection'])->update(['type' => 'int']);
        Options::where(['name' => 'noobprotectiontime'])->update(['type' => 'int']);
        Options::where(['name' => 'noobprotectionmulti'])->update(['type' => 'int']);
        Options::where(['name' => 'modules'])->update(['type' => 'string']);
        Options::where(['name' => 'admin_permissions'])->update(['type' => 'string']);
        Options::where(['name' => 'initial_fields'])->update(['type' => 'int']);
        Options::where(['name' => 'metal_basic_income'])->update(['type' => 'int']);
        Options::where(['name' => 'crystal_basic_income'])->update(['type' => 'int']);
        Options::where(['name' => 'deuterium_basic_income'])->update(['type' => 'int']);
        Options::where(['name' => 'energy_basic_income'])->update(['type' => 'int']);
        Options::where(['name' => 'reg_enable'])->update(['type' => 'int']);
        Options::where(['name' => 'reg_welcome_message'])->update(['type' => 'int']);
        Options::where(['name' => 'reg_welcome_email'])->update(['type' => 'int']);
        Options::where(['name' => 'stat_points'])->update(['type' => 'int']);
        Options::where(['name' => 'stat_update_time'])->update(['type' => 'int']);
        Options::where(['name' => 'stat_admin_level'])->update(['type' => 'int']);
        Options::where(['name' => 'stat_last_update'])->update(['type' => 'int']);
        Options::where(['name' => 'premium_url'])->update(['type' => 'string']);
        Options::where(['name' => 'merchant_price'])->update(['type' => 'int']);
        Options::where(['name' => 'auto_backup'])->update(['type' => 'int']);
        Options::where(['name' => 'last_backup'])->update(['type' => 'int']);
        Options::where(['name' => 'last_cleanup'])->update(['type' => 'int']);
        Options::where(['name' => 'version'])->update(['type' => 'string']);
        Options::where(['name' => 'lastsettedgalaxypos'])->update(['type' => 'int']);
        Options::where(['name' => 'lastsettedsystempos'])->update(['type' => 'int']);
        Options::where(['name' => 'lastsettedplanetpos'])->update(['type' => 'int']);
        Options::where(['name' => 'merchant_base_min_exchange_rate'])->update(['type' => 'float']);
        Options::where(['name' => 'merchant_base_max_exchange_rate'])->update(['type' => 'float']);
        Options::where(['name' => 'merchant_metal_multiplier'])->update(['type' => 'int']);
        Options::where(['name' => 'merchant_crystal_multiplier'])->update(['type' => 'int']);
        Options::where(['name' => 'merchant_deuterium_multiplier'])->update(['type' => 'int']);
        Options::where(['name' => 'registration_dark_matter'])->update(['type' => 'int']);
        Options::where(['name' => 'mailing_protocol'])->update(['type' => 'string']);
        Options::where(['name' => 'mailing_smtp_host'])->update(['type' => 'string']);
        Options::where(['name' => 'mailing_smtp_user'])->update(['type' => 'string']);
        Options::where(['name' => 'mailing_smtp_pass'])->update(['type' => 'string']);
        Options::where(['name' => 'mailing_smtp_port'])->update(['type' => 'int']);
        Options::where(['name' => 'mailing_smtp_timeout'])->update(['type' => 'int']);
        Options::where(['name' => 'mailing_smtp_crypto'])->update(['type' => 'string']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
