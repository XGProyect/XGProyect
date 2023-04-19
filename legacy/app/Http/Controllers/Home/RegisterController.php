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
            die(Functions::message(__('home/register.re_disabled'), 'index.php', '5', false, false));
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
                        '',
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
        $lastGalaxy = (int) Functions::readConfig('lastsettedgalaxypos');
        $lastSystem = (int) Functions::readConfig('lastsettedsystempos');
        $lastPlanet = (int) Functions::readConfig('lastsettedplanetpos');

        while (true) {
            for ($galaxy = $lastGalaxy; $galaxy <= MAX_GALAXY_IN_WORLD; $galaxy++) {
                for ($system = $lastSystem; $system <= MAX_SYSTEM_IN_GALAXY; $system++) {
                    for ($pos = $lastPlanet; $pos <= 4; $pos++) {
                        $planet = mt_rand(4, 12);

                        switch ($lastPlanet) {
                            case 1:
                                $lastPlanet += 1;

                                break;

                            case 2:
                                $lastPlanet += 1;

                                break;

                            case 3:
                                if ($lastSystem == MAX_SYSTEM_IN_GALAXY) {
                                    $lastGalaxy += 1;
                                    $lastSystem = 1;
                                    $lastPlanet = 1;

                                    break;
                                } else {
                                    $lastPlanet = 1;
                                }

                                $lastSystem += 1;

                                break;
                        }
                        break;
                    }
                    break;
                }
                break;
            }

            if (!$this->registerModel->checkIfPlanetExists($galaxy, $system, $planet)) {
                Functions::updateConfig('lastsettedgalaxypos', $lastGalaxy);
                Functions::updateConfig('lastsettedsystempos', $lastSystem);
                Functions::updateConfig('lastsettedplanetpos', $lastPlanet);

                $this->available_coords = [
                    'galaxy' => $galaxy,
                    'system' => $system,
                    'planet' => $planet,
                ];

                return;
            }
        }
    }
}
