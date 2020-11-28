<?php declare (strict_types = 1);
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
namespace Xgp\Lobby\Models;

use App\Models\BaseModel;

/**
 * Home model
 */
class Home extends BaseModel
{
    /**
     * Get the user based on the provided credentials
     *
     * @param string $email
     * @return array|null
     */
    public function getUserWithProvidedCredentials(string $email): ?object
    {
        $user = $this->db->table($this->prefix(USERS))
            ->select('user_id, user_name, user_password, banned_longer')
            ->join($this->prefix(BANNED), 'banned_who = user_name', 'left')
            ->where('user_email', $email)
            ->get()
            ->getRow();

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
    public function removeBan(string $user_name): void
    {
        $this->db->table($this->prefix(BANNED))->delete(['banned_who' => $user_name]);
    }
}
