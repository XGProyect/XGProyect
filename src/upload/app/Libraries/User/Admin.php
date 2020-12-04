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
 * @since      4.0.0
 */
namespace App\Libraries\User;

use App\Libraries\Session;
use App\Libraries\User\User;

class Admin extends User
{
    /**
     * Start the login
     *
     * @param integer $id
     * @param string $password
     * @return boolean
     */
    public function doLogin(int $id, string $password): bool
    {
        if ($id != 0 && !empty($password)) {
            helper('password');

            $this->session->setAdminSession($id, pswHash($password));

            return true;
        }

        return false;
    }

    private function setAdmin()
    {
        if ($this->session->isAdminSessionSet()) {
            $this->setCurrentAdminData();
        }
    }

    private function setCurrentAdminData(): void
    {
    }
}
