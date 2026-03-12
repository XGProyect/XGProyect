<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\Alliance\Ranks;
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

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Users();
        }

        return self::$instance;
    }

    public function getUserData(): array
    {
        return $this->userData;
    }

    public function getPlanetData(): array
    {
        return $this->planetData;
    }

    public function deleteUser(int $userId): void
    {
        $userData = $this->usersModel->getAllyIdByUserId($userId);

        if ($userData['ally_id'] != 0) {
            $alliance = $this->usersModel->getAllianceDataByAllianceId($userData['ally_id']);

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
        return ($user['onlinetime'] < (time() - ONE_WEEK));
    }

    private static function isSessionSet(): bool
    {
        return session('user_id', false) && session('user_password', false);
    }

    private function setUserData(): void
    {
        $userRow = $this->usersModel->setUserDataByUserId();

        $this->displayLoginErrors($userRow);

        // update user activity data
        $this->usersModel->updateUserActivityData(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            session('user_id')
        );

        // pass the data
        $this->userData = $userRow;

        // unset the old data
        unset($userRow);
    }

    private function displayLoginErrors(array $userRow): void
    {
        if ($userRow['id'] != session('user_id')) {
            Functions::redirect(SYSTEM_ROOT);
        }

        if (Auth::id() !== session('user_id')) {
            Functions::redirect(SYSTEM_ROOT);
        }

        if (!Hash::check(($userRow['password'] . '-' . config('SECRETWORD')), session('user_password'))) {
            Functions::redirect(SYSTEM_ROOT);
        }
    }

    private function setPlanetData(): void
    {
        $this->planetData = $this->usersModel->setPlanetData(
            (int) $this->userData['current_planet'],
            (int) Options::getInstance()->get('stat_admin_level')
        );
    }

    private function setPlanet(): void
    {
        $select = isset($_GET['cp']) ? (int) $_GET['cp'] : '';
        $restore = isset($_GET['re']) ? (int) $_GET['re'] : '';

        if (is_numeric($select) && $restore == 0 && $select != 0) {
            $owned = $this->usersModel->getUserPlanetByIdAndUserId($select, (int) $this->userData['id']);

            if ($owned) {
                $this->userData['current_planet'] = $select;
                $this->usersModel->changeUserPlanetByUserId($select, (int) $this->userData['id']);
            }
        }
    }
}
