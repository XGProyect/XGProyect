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
namespace App\Libraries\User;

use App\Libraries\Session;
use App\Libraries\User\User;

class Player extends User
{
    /**
     * Sign in the player into the game
     *
     * @param integer $id
     * @param string $password
     * @return boolean
     */
    public function doLogin(int $id, string $password): bool
    {
        if ($id != 0 && !empty($password)) {
            $this->session->setUserSession($id, pswHash($password));

            return true;
        }

        return false;
    }

    /**
     * Sign out the player from the game
     *
     * @return void
     */
    public function doLogout()
    {
        if ($this->session->isUserSessionSet()) {
            $this->session->destroyUserSession();
        }
    }

    public function setPlayer()
    {
        if ($this->session->isUserSessionSet()) {
            $this->setCurrentPlayerData();
        }
    }

    private function setCurrentPlayerData(): void
    {
    }
}
