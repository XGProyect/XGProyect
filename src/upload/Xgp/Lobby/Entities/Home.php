<?php

namespace Xgp\Lobby\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Xgp\Lobby\Database\factories\HomeFactory;

class Home extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return HomeFactory::new ();
    }

    /**
     * Get the user based on the provided credentials
     *
     * @param string $email
     * @return array|null
     */
    public function getUserWithProvidedCredentials(string $email): ?object
    {
        $user = DB::table(config('constants.tables.USERS'))
            ->select('user_id', 'user_name', 'user_password', 'banned_longer')
            ->leftJoin(config('constants.tables.BANNED'), 'banned_who', '=', 'user_name')
            ->where('user_email', $email)
            ->get()
            ->first();

        if (isset($user->banned_longer) && $user->banned_longer <= time()) {
            $this->removeBan($user->user_name);
        }

        return $user;
    }

    /**
     * Remove ban
     *
     * @param string $user_name
     * @return void
     */
    public function removeBan(string $userName): void
    {
        DB::table(config('constants.tables.BANNED'))->where(['banned_who' => $userName])->delete();
    }
}
