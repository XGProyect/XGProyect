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

class User
{
    /**
     * Contains a Session
     *
     * @var App\Libraries\Session
     */
    protected $session;

    /**
     * Contains the user properties
     *
     * @var
     */
    protected $userAttributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setSession();
    }

    /**
     * Start the login
     *
     * @param integer $id
     * @param string $password
     * @return boolean
     */
    protected function doLogin(int $id, string $password): bool
    {
    }

    /**
     * Get user authorization level
     *
     * @return integer
     */
    public function getAuthLevel(): int
    {
        return $this->userAttributes->getUserAuthLevel();
    }

    /**
     * Set the user properties
     *
     * @return void
     */
    protected function setUserProperties(): void
    {
        $this->userAttributes = new UserAtrributes();
    }

    /**
     * Start a new session object
     *
     * @return void
     */
    private function setSession(): void
    {
        $this->session = new Session;
    }
}
