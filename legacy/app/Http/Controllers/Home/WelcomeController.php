<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Home;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Home\Home;

class WelcomeController extends BaseController
{
    private Home $homeModel;
    private Users $userLibrary;

    public function __invoke(): void
    {
        $this->homeModel = new Home();
        $this->userLibrary = Users::getInstance();

        $this->runAction();

        Template::getInstance()->view(
            'home.welcome',
            array_merge(
                $this->getErrors(),
                $this->getPageData()
            )
        );
    }

    private function runAction(): void
    {
        $loginData = filter_input_array(INPUT_POST, [
            'login' => FILTER_VALIDATE_EMAIL,
            'pass' => FILTER_UNSAFE_RAW,
        ]);

        if (!empty($loginData['login']) && !empty($loginData['pass'])) {
            $login = $this->homeModel->getUserWithProvidedCredentials($loginData['login']);

            if (isset($login) && password_verify($loginData['pass'], $login['user_password'])) {
                if (isset($login['banned_longer']) && $login['banned_longer'] <= time()) {
                    $this->homeModel->removeBan($login['user_name']);
                }

                if ($this->userLibrary->userLogin((int) $login['user_id'], $login['user_password'])) {
                    $this->homeModel->setUserHomeCurrentPlanet((int) $login['user_id']);

                    // redirect to game
                    Functions::redirect('game.php?page=overview');
                }
            }

            // if login failed
            Functions::redirect('index.php');
        }
    }

    private function getPageData(): array
    {
        return [
            'servername' => __('home/home.hm_title', ['game' => Functions::readConfig('game_name')]),
            'gameLogo' => Functions::readConfig('game_logo'),
            'extraJsError' => $this->getErrors(),
            'basePath' => BASE_PATH,
            'userName' => isset($_GET['character']) ? $_GET['character'] : '',
            'userEmail' => isset($_GET['email']) ? $_GET['email'] : '',
            'forumUrl' => Functions::readConfig('forum_url'),
            'version' => SYSTEM_VERSION,
            'year' => date('Y'),
        ];
    }

    private function getErrors(): array
    {
        $errors = filter_input(INPUT_GET, 'error', FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 0,
                'min_range' => 1,
                'max_range' => 2,
            ],
        ]);

        switch ($errors) {
            case 1:
                $div_id = '#username';
                $message = __('home/home.hm_username_not_available');
                break;

            case 2:
                $div_id = '#email';
                $message = __('home/home.hm_email_not_available');
                break;

            case 0:
            default:
                $div_id = '';
                $message = '';
                break;
        }

        return [
            'divId' => $div_id,
            'message' => $message,
        ];
    }
}
