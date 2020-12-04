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
namespace App\Libraries;

class Session
{
    /**
     * Set a player session
     *
     * @param integer $user_id
     * @param string $user_password
     * @return void
     */
    public function setUserSession(int $user_id, string $user_password): void
    {
        session([
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
        session([
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
        return (session()->has('user_id') && session()->has('user_password'));
    }

    /**
     * Check if an admin session was set
     *
     * @return boolean
     */
    public function isAdminSessionSet(): bool
    {
        return (session()->has('admin_id') && session()->has('admin_password'));
    }

    /**
     * Destroy a player session
     *
     * @return void
     */
    public function destroyUserSession(): void
    {
        session()->forget(['user_id', 'user_password']);
    }

    /**
     * Destroy an admin session
     *
     * @return void
     */
    public function destroyAdminSession(): void
    {
        session()->forget(['admin_id', 'admin_password']);
    }
}
