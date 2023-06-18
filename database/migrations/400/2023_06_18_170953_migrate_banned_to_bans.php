<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Map usernames to user_ids
        $bans = DB::table('bans')
            ->join('users', 'users.name', '=', 'bans.banned_who')
            ->select('bans.id', 'users.id as user_id')
            ->get();

        foreach ($bans as $ban) {
            DB::table('bans')
                ->where('id', $ban->id)
                ->update(['user_id' => $ban->user_id]);
        }

        // Map usernames to admin_ids
        $bans = DB::table('bans')
            ->join('users', 'users.name', '=', 'bans.banned_author')
            ->select('bans.id', 'users.id as user_id')
            ->get();

        foreach ($bans as $ban) {
            DB::table('bans')
                ->where('id', $ban->id)
                ->update(['admin_id' => $ban->user_id]);
        }

        // Move data back to the old columns
        $bans = DB::table('bans')->get();

        foreach ($bans as $ban) {
            $bannedTime = Carbon::createFromTimestamp($ban->banned_time)->toDateTimeString();
            $bannedLonger = Carbon::createFromTimestamp($ban->banned_longer)->toDateTimeString();

            DB::table('bans')
                ->where('id', $ban->id)
                ->update([
                    'details' => $ban->banned_theme,
                    'created_at' => $bannedTime,
                    'updated_at' => DB::raw('NOW()'),
                    'until' => $bannedLonger,
                ]);
        }

        Schema::table('bans', function (Blueprint $table) {
            $table->dropColumn('banned_who');
            $table->dropColumn('banned_theme');
            $table->dropColumn('banned_time');
            $table->dropColumn('banned_longer');
            $table->dropColumn('banned_author');
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
