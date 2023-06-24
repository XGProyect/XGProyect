<?php

declare(strict_types=1);

namespace App\Http\Controllers\Home;

use App\Services\SettingsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class WelcomeController extends BaseController
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    public function __invoke(Request $request): View|Factory
    {
        return view(
            'home.welcome',
            array_merge(
                [
                    'servername' => __('home/welcome.hm_title', ['game' => $this->settingsService->getString('game_name')]),
                    'gameLogo' => $this->settingsService->getString('game_logo'),
                    'basePath' => url('/'),
                    'userName' => '',
                    'userEmail' => '',
                    'forumUrl' => $this->settingsService->getString('forum_url'),
                ],
                $this->getErrors($request)
            )
        );
    }

    private function getErrors(Request $request): array
    {
        $errorsBlocks = [];
        $loginError = false;
        $errorsBags = $request->getSession()->get('errors');

        if ($errorsBags !== null) {
            if ($errorsBags->hasBag('login')) {
                $loginError = true;

                foreach ($errorsBags->getBag('login')->getMessages() as $field => $errors) {
                    $errorsBlocks[] = [
                        'divId' => '#' . $field . 'Login',
                        'message' => $errors[0], // first error only
                    ];
                }
            }

            if ($errorsBags->hasBag('register')) {
                foreach ($errorsBags->getBag('register')->getMessages() as $field => $errors) {
                    $errorsBlocks[] = [
                        'divId' => '#' . $field,
                        'message' => $errors[0], // first error only
                    ];
                }
            }
        }

        return [
            'errors' => $errorsBlocks,
            'loginError' => $loginError,
        ];
    }
}
