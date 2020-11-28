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

class UserAttributes
{
    public $id;
    public $userName;
    public $password;
    public $email;
    public $authLevel;

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function setId(int $value): void
    {
        $this->id = $value;
    }

    public function getUserName(): string
    {
        return (string) $this->userName;
    }

    public function setUserName(string $value): void
    {
        $this->userName = $value;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $value): void
    {
        $this->password = $value;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function setEmail(string $value): void
    {
        $this->email = $value;
    }

    public function getAuthLevel(): int
    {
        return (int) $this->authLevel;
    }

    public function setAuthLevel(int $value): void
    {
        $this->authLevel = $value;
    }
}
