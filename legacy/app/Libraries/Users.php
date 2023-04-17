<?php

namespace Xgp\App\Libraries;

use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Libraries\Alliance\Ranks;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Libraries\UsersLibrary;

class Users
{
    private array $userData = [];
    private array $planetData = [];
    private UsersLibrary $usersModel;
    private static ?Users $instance = null;

    public function __construct()
    {
        $this->usersModel = new UsersLibrary();

        if (self::isSessionSet()) {
            // Get user data and check it
            $this->setUserData();

            if (!defined('IN_ADMIN')) {
                // Set the changed planet
                $this->setPlanet();

                // Get planet data and check it
                $this->setPlanetData();

                // Update resources, ships, defenses & technologies
                UpdatesLibrary::updatePlanetResources($this->userData, $this->planetData, time());

                // Update buildings queue
                UpdatesLibrary::updateBuildingsQueue($this->planetData, $this->userData);
            }
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Users();
        }

        return self::$instance;
    }

    /**
     * userLogin
     *
     * @param int    $userId   User ID
     * @param string $password  Password
     *
     * @return void
     */
    public function userLogin($userId = 0, $password = '')
    {
        if ($userId != 0 && !empty($password) && (strlen($password) == 60)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_password'] = Functions::hash($password . '-' . config('SECRETWORD'));

            return true;
        } else {
            return false;
        }
    }

    public function getUserData(): array
    {
        return $this->userData;
    }

    public function getPlanetData(): array
    {
        return $this->planetData;
    }

    public static function checkSession(): void
    {
        if (!self::isSessionSet()) {
            Functions::redirect(SYSTEM_ROOT);
        }
    }

    public function deleteUser(int $userId): void
    {
        $userData = $this->usersModel->getAllyIdByUserId($userId);

        if ($userData['user_ally_id'] != 0) {
            $alliance = $this->usersModel->getAllianceDataByAllianceId($userData['user_ally_id']);

            if ($alliance['ally_members'] > 1 && (isset($alliance['alliance_ranks']) && !is_null($alliance['alliance_ranks']))) {
                $ranks = new Ranks($alliance['alliance_ranks']);
                $userRank = null;

                // search for an user that has permission to receive the alliance.
                foreach ($ranks->getAllRanksAsArray() as $id => $rank) {
                    if (isset($rank['rights'][AllianceRanks::RIGHT_HAND]) && $rank['rights'][AllianceRanks::RIGHT_HAND] == SwitchInt::on) {
                        $userRank = $id;
                        break;
                    }
                }

                // check and update
                if (is_numeric($userRank)) {
                    $this->usersModel->updateAllianceOwner($alliance['alliance_id'], $userRank);
                } else {
                    $this->usersModel->deleteAllianceById($alliance['alliance_id']);
                }
            } else {
                $this->usersModel->deleteAllianceById($alliance['alliance_id']);
            }
        }

        $this->usersModel->deletePlanetsAndRelatedDataByUserId($userId);
        $this->usersModel->deleteMessagesByUserId($userId);
        $this->usersModel->deleteBuddysByUserId($userId);
        $this->usersModel->deleteUserDataById($userId);
    }

    public function isOnVacations(array $user): bool
    {
        return ($user['preference_vacation_mode'] > 0);
    }

    public function isInactive(array $user): bool
    {
        return ($user['user_onlinetime'] < (time() - ONE_WEEK));
    }

    private static function isSessionSet(): bool
    {
        return !(!isset($_SESSION['user_id']) or !isset($_SESSION['user_password']));
    }

    private function setUserData(): void
    {
        $userRow = $this->usersModel->setUserDataByUserId($_SESSION['user_id']);

        $this->displayLoginErrors($userRow);

        // update user activity data
        $this->usersModel->updateUserActivityData(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            $_SESSION['user_id']
        );

        // pass the data
        $this->userData = $userRow;

        // unset the old data
        unset($userRow);
    }

    private function displayLoginErrors(array $userRow): void
    {
        if ($userRow['user_id'] != $_SESSION['user_id'] && !defined('IN_LOGIN')) {
            Functions::redirect(SYSTEM_ROOT);
        }

        if (!password_verify(($userRow['user_password'] . "-" . config('SECRETWORD')), $_SESSION['user_password']) && !defined('IN_LOGIN')) {
            Functions::redirect(SYSTEM_ROOT);
        }
    }

    private function setPlanetData(): void
    {
        $this->planetData = $this->usersModel->setPlanetData(
            $this->userData['user_current_planet'],
            Functions::readConfig('stat_admin_level')
        );
    }

    private function setPlanet(): void
    {
        $select = isset($_GET['cp']) ? (int) $_GET['cp'] : '';
        $restore = isset($_GET['re']) ? (int) $_GET['re'] : '';

        if (isset($select) && is_numeric($select) && isset($restore) && $restore == 0 && $select != 0) {
            $owned = $this->usersModel->getUserPlanetByIdAndUserId($select, $this->userData['user_id']);

            if ($owned) {
                $this->userData['current_planet'] = $select;
                $this->usersModel->changeUserPlanetByUserId($select, $this->userData['user_id']);
            }
        }
    }

    public function createUserWithOptions(array $data, $full_insert = true): int
    {
        $insert_query = 'INSERT INTO ' . USERS . ' SET ';

        foreach ($data as $column => $value) {
            $insert_query .= "`" . $column . "` = '" . $value . "', ";
        }

        // Remove last comma
        $insert_query = substr_replace($insert_query, '', -2) . ';';

        // get the last inserted user id
        $userId = $this->usersModel->createNewUser($insert_query);

        // insert extra required tables
        if ($full_insert) {
            // create the buildings, defenses and ships tables
            $this->usersModel->createPremium($userId);
            $this->usersModel->createResearch($userId);
            $this->usersModel->createSettings($userId);
            $this->usersModel->createUserStatistics($userId);
        }

        return $userId;
    }
}
