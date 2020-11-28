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
namespace App\Libraries;

use Config\Services;

class Session
{
    /**
     * Contains a Session
     *
     * @var \CodeIgniter\Session\Session
     */
    private $session;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->session = Services::session();
    }

    /**
     * Set a player session
     *
     * @param integer $user_id
     * @param string $user_password
     * @return void
     */
    public function setUserSession(int $user_id, string $user_password): void
    {
        $this->session->set([
            'user_id' => $user_id,
            'user_password' => $user_password,
        ]);
    }

    /**
     * Set an admin session
     *
     * @param integer $admin_id
     * @param string $admin_password
     * @return void
     */
    public function setAdminSession(int $admin_id, string $admin_password): void
    {
        $this->session->set([
            'admin_id' => $admin_id,
            'admin_password' => $admin_password,
        ]);
    }

    /**
     * Check if a player session was set
     *
     * @return boolean
     */
    public function isUserSessionSet(): bool
    {
        return ($this->session->has('user_id') && $this->session->has('user_password'));
    }

    /**
     * Check if an admin session was set
     *
     * @return boolean
     */
    public function isAdminSessionSet(): bool
    {
        return ($this->session->has('admin_id') && $this->session->has('admin_password'));
    }

    /**
     * Destroy a player session
     *
     * @return void
     */
    public function destroyUserSession(): void
    {
        $this->session->remove('user_id');
        $this->session->remove('user_password');
        $this->session->destroy();
    }

    /**
     * Destroy an admin session
     *
     * @return void
     */
    public function destroyAdminSession(): void
    {
        $this->session->remove('admin_id');
        $this->session->remove('admin_password');
        $this->session->destroy();
    }
}
