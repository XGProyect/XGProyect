<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('user_id', 'id');
            $table->renameColumn('user_name', 'name');
            $table->renameColumn('user_password', 'password');
            $table->renameColumn('user_email', 'email');
            $table->timestamp('email_verified_at')->nullable()->after('user_email');
            $table->renameColumn('user_authlevel', 'authlevel');
            $table->renameColumn('user_home_planet_id', 'home_planet_id');
            $table->renameColumn('user_galaxy', 'galaxy');
            $table->renameColumn('user_system', 'system');
            $table->renameColumn('user_planet', 'planet');
            $table->renameColumn('user_current_planet', 'current_planet');
            $table->renameColumn('user_lastip', 'lastip');
            $table->renameColumn('user_ip_at_reg', 'ip_at_reg');
            $table->renameColumn('user_agent', 'agent');
            $table->renameColumn('user_current_page', 'current_page');
            $table->renameColumn('user_register_time', 'register_time');
            $table->renameColumn('user_onlinetime', 'onlinetime');
            $table->renameColumn('user_fleet_shortcuts', 'fleet_shortcuts');
            $table->renameColumn('user_ally_id', 'ally_id');
            $table->renameColumn('user_ally_request', 'ally_request');
            $table->renameColumn('user_ally_request_text', 'ally_request_text');
            $table->renameColumn('user_ally_register_time', 'ally_register_time');
            $table->renameColumn('user_ally_rank_id', 'ally_rank_id');
            $table->renameColumn('user_banned', 'banned');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
