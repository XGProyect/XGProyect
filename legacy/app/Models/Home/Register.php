<?php

declare(strict_types=1);

namespace Xgp\App\Models\Home;

use Exception;
use Xgp\App\Core\Model;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\PlanetLib;
use Xgp\App\Libraries\Users;

class Register extends Model
{
    private int $userId = 0;
    private string $userName = '';
    private string $userEmail = '';
    private string $userPassword = '';

    public function checkIfPlanetExists(int $galaxy, int $system, int $planet): bool
    {
        $planet = $this->db->queryFetch(
            'SELECT
                `planet_id`
            FROM `' . PLANETS . "`
            WHERE `planet_galaxy` = '" . $galaxy . "'
                AND `planet_system` = '" . $system . "'
                AND `planet_planet` = '" . $planet . "'
            LIMIT 1;"
        );

        return isset($planet['planet_id']);
    }

    public function createNewUser(Users $user, array $newUserData, array $coords): void
    {
        try {
            $this->db->beginTransaction();

            $this->userName = $this->db->escapeValue(strip_tags($newUserData['new_user_name']));
            $this->userEmail = $this->db->escapeValue($newUserData['new_user_email']);
            $this->userPassword = Functions::hash($newUserData['new_user_password']);

            // create the new user
            $this->userId = $user->createUserWithOptions(
                [
                    'user_name' => $this->userName,
                    'user_password' => $this->userPassword,
                    'user_email' => $this->userEmail,
                    'user_lastip' => $_SERVER['REMOTE_ADDR'],
                    'user_ip_at_reg' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'user_current_page' => $this->db->escapeValue($_SERVER['REQUEST_URI']),
                    'user_register_time' => time(),
                    'user_onlinetime' => time(),
                ]
            );

            // create a new planet
            $this->createNewPlanet($coords, $this->userId);

            // assign the new planet to the new user
            $this->updateUserPlanet($coords, $this->userId);

            $this->db->commitTransaction();
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
        }
    }

    private function createNewPlanet(array $coords, int $new_user_id): void
    {
        $creator = new PlanetLib();
        $creator->setNewPlanet($coords['galaxy'], $coords['system'], $coords['planet'], $new_user_id, '', true);
    }

    private function updateUserPlanet(array $coords, int $new_user_id): void
    {
        $this->db->query(
            'UPDATE `' . USERS . '` SET
            `user_home_planet_id` = (
                SELECT
                    `planet_id`
                FROM `' . PLANETS . "`
                WHERE `planet_user_id` = '" . $new_user_id . "'
                LIMIT 1
            ),
            `user_current_planet` = (
                SELECT
                    `planet_id`
                FROM `" . PLANETS . "`
                WHERE `planet_user_id` = '" . $new_user_id . "'
                LIMIT 1
            ),
            `user_galaxy` = '" . $coords['galaxy'] . "',
            `user_system` = '" . $coords['system'] . "',
            `user_planet` = '" . $coords['planet'] . "'
             WHERE `user_id` = '" . $new_user_id . "' LIMIT 1;"
        );
    }

    public function getNewUserData(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
            'user_hashed_password' => $this->userPassword,
        ];
    }

    public function checkUser(string $userName): ?array
    {
        return $this->db->queryFetch(
            'SELECT
                u.`user_name`
            FROM `' . USERS . "` AS u
            WHERE `user_name` = '" . $this->db->escapeValue($userName) . "'
            LIMIT 1;"
        );
    }

    public function checkEmail(string $email): ?array
    {
        return $this->db->queryFetch(
            'SELECT
                u.`user_email`
            FROM `' . USERS . "` AS u
            WHERE `user_email` = '" . $this->db->escapeValue($email) . "'
            LIMIT 1;"
        );
    }
}
