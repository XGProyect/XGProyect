<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Home;

use App\Mail\Welcome;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Home\Register;

class RegisterController extends BaseController
{
    private array $available_coords = [];
    private int $error_id;
    private Register $registerModel;
    private Users $userLibrary;

    public function __invoke(): void
    {
        $this->registerModel = new Register();
        $this->userLibrary = Users::getInstance();

        if (Functions::readConfig('reg_enable') != 1) {
            Functions::message(__('home/register.re_disabled'), 'index.php', '5', false, false);
            exit;
        }

        if ($_POST) {
            $userName = $_POST['character'];
            $userEmail = $_POST['email'];
            $userPassword = $_POST['password'];

            if (!$this->runValidations()) {
                if ($this->error_id != '') {
                    $url = 'index.php?character=' . $userName . '&email=' . $userEmail . '&error=' . $this->error_id;
                } else {
                    $url = 'index.php';
                }

                Functions::redirect(SYSTEM_ROOT . $url);
            } else {
                // start user creation
                $this->calculateNewPlanetPosition();

                $this->registerModel->createNewUser(
                    $this->userLibrary,
                    [
                        'new_user_name' => $userName,
                        'new_user_email' => $userEmail,
                        'new_user_password' => $userPassword,
                    ],
                    $this->available_coords
                );

                $newUser = $this->registerModel->getNewUserData();

                // Send Welcome Message to the user if the feature is enabled
                if (Functions::readConfig('reg_welcome_message')) {
                    Functions::sendMessage(
                        $newUser['user_id'],
                        0,
                        0,
                        5,
                        __('home/register.re_welcome_message_from'),
                        __('home/register.re_welcome_message_subject'),
                        str_replace('%s', $newUser['user_name'], __('home/register.re_welcome_message_content'))
                    );
                }

                // Send Welcome Email to the user if the feature is enabled
                if (Functions::readConfig('reg_welcome_email')) {
                    Mail::to($newUser['user_email'])->send(new Welcome(
                        $newUser['user_name'],
                        $userPassword
                    ));
                }

                // User login
                if ($this->userLibrary->userLogin($newUser['user_id'], $newUser['user_hashed_password'])) {
                    // Redirect to game
                    Functions::redirect(SYSTEM_ROOT . 'game.php?page=overview');
                }
            }
        }

        // If login fails
        Functions::redirect('index.php');
    }

    private function runValidations(): bool
    {
        $errors = 0;

        if (!Functions::validEmail($_POST['email'])) {
            $errors++;
        }

        if (!$_POST['character']) {
            $errors++;
        }

        if (strlen($_POST['password']) < 8) {
            $errors++;
        }

        if (preg_match("/[^A-z0-9_\-]/", $_POST['character']) == 1) {
            $errors++;
        }

        if ($_POST['agb'] != 'on') {
            $errors++;
        }

        if ($this->registerModel->checkUser($_POST['character'])) {
            $errors++;
            $this->error_id = 1;
        }

        if ($this->registerModel->checkEmail($_POST['email'])) {
            $errors++;
            $this->error_id = 2;
        }

        return ($errors <= 0);
    }

    private function calculateNewPlanetPosition(): void
    {
        $this->isPlanetFree(
            (int) Functions::readConfig('lastsettedgalaxypos'),
            (int) Functions::readConfig('lastsettedsystempos'),
            max((int) Functions::readConfig('lastsettedplanetpos'), 4) // new users need to start at position 4
        );
    }

    private function isPlanetFree($galaxy, $system, $position)
    {
        // Check if the planet is free
        $isFree = !$this->registerModel->checkIfPlanetExists($galaxy, $system, $position);
        if ($isFree) {
            Functions::updateConfig('lastsettedgalaxypos', $galaxy);
            Functions::updateConfig('lastsettedsystempos', $system);
            Functions::updateConfig('lastsettedplanetpos', $position);

            $this->available_coords = [
                'galaxy' => $galaxy,
                'system' => $system,
                'planet' => $position,
            ];

            return true;
        }

        // If the planet is not free, try the next position
        if ($position < 12) {
            return $this->isPlanetFree($galaxy, $system, $position + PLANET_SEPARATION_FACTOR);
        }

        // If we've tried all positions in this system, try the next system
        if ($system < MAX_SYSTEM_IN_GALAXY) {
            return $this->isPlanetFree($galaxy, $system + SYSTEM_SEPARATION_FACTOR, 4);
        }

        // If we've tried all systems in this galaxy, try the next galaxy
        if ($galaxy < MAX_GALAXY_IN_WORLD) {
            return $this->isPlanetFree($galaxy + GALAXY_SEPARATION_FACTOR, 1, 4);
        }

        // If we've tried all galaxies and haven't found a free planet, restart the search
        return $this->isPlanetFree(1, 1, 4);
    }
}
