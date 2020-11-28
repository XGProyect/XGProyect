<?php
/**
 * XG Proyect
 *
 * Open-source OGame Clon
 *
 * This content is released under the GPL-3.0 License
 *
 * Copyright (c) 2008-2021 XG Proyect
 *
 * @package    XG Proyect
 * @author     XG Proyect Team
 * @copyright  2008-2021 XG Proyect
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0 License
 * @link       https://github.com/XGProyect/
 * @since      Version 4.0.0
 */
namespace App\Models;

use App\Models\PreferenceModel;
use App\Models\PremiumModel;
use App\Models\ResearchModel;
use App\Models\UserStatisticModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
    protected $fillable = [
        'user_name',
        'user_password',
        'user_email',
        'user_authlevel',
        'user_current_planet',
        'user_lastip',
        'user_agent',
        'user_current_page',
        'user_onlinetime',
        'user_fleet_shortcuts',
        'user_ally_id',
        'user_ally_request',
        'user_ally_request_text',
        'user_ally_register_time',
        'user_ally_rank_id',
        'user_banned',
    ];
    protected $returnType = 'App\Entities\User';

    /**
     * Create a new user
     *
     * @param array $data
     * @return integer
     */
    public function create(array $data): int
    {
        $this->db->transStart();

        $environment_data = [
            'user_lastip' => $_SERVER['REMOTE_ADDR'],
            'user_ip_at_reg' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'user_current_page' => $_SERVER['REQUEST_URI'],
            'user_register_time' => time(),
            'user_onlinetime' => time(),
        ];

        $this->save((new User(array_merge($data, $environment_data))));

        $newUserId = $this->db->insertID();

        (new PreferenceModel)->save(new Preference(['preference_user_id' => $newUserId]));
        (new PremiumModel)->save(new Premium(['premium_user_id' => $newUserId]));
        (new ResearchModel)->save(new Research(['research_user_id' => $newUserId]));
        (new UserStatisticModel)->save(new UserStatistic(['user_statistic_user_id' => $newUserId]));

        $this->db->transComplete();

        return ($this->db->transStatus() === true ? $newUserId : 0);
    }

    /**
     * Reset the user current planet to the oldest possible
     *
     * @param integer $userId
     * @return void
     */
    public function setCurrentPlanet(int $userId): void
    {
        DB::table(config('constants.tables.USERS'))
            ->where('user_id', $userId)
            ->update([
                'user_current_planet' => DB::raw(
                    "(SELECT
                        MIN(`planet_id`)
                    FROM `" . DB::getTablePrefix() . config('constants.tables.PLANETS') . "`
                    WHERE
                        `planet_user_id` = '" . $userId . "'
                    AND
                        `planet_type` = 1
                    AND
                        `planet_destroyed` = 0)"
                ),
            ]);
    }

    /**
     * Check if email exists returning the user name
     *
     * @param string $email
     * @return string|null
     */
    public function getUsernameByEmail(string $email): string
    {
        $user = $this->db->table(config('constants.tables.USERS'))
            ->select('user_name')
            ->where('user_email', $email)
            ->get()
            ->getRow();

        return isset($user) ? $user->user_name : '';
    }

    /**
     * Set a new password for the user
     *
     * @param string $email
     * @param string $newPassword
     * @return void
     */
    public function updatePassword(string $email, string $newPassword): void
    {
        $this->db->table(config('constants.tables.USERS'))
            ->set('user_password', pswHash($newPassword))
            ->where('user_email', $email)
            ->update();
    }
}
