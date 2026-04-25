<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\LegacyView;
use App\Http\Controllers\Game as Game;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class LegacyController extends BaseController
{
    /**
     * Game pages promoted to Laravel controllers.
     * These bypass the legacy bootstrap entirely.
     *
     * @var array<string, class-string>
     */
    private const PROMOTED_PAGES = [
        'banned' => Game\BannedController::class,
        'changelog' => Game\ChangelogController::class,
        'logout' => Game\LogoutController::class,
        'technologydetails' => Game\TechnologydetailsController::class,
    ];

    public function __invoke(Request $request): BaseResponse
    {
        $file = strtr($request->getPathInfo(), ['/' => '', '.php' => '']);

        if ($file === 'game') {
            $page = $request->query('page');

            if (is_string($page) && isset(self::PROMOTED_PAGES[$page])) {
                $result = app()->call(self::PROMOTED_PAGES[$page]);

                return $result instanceof BaseResponse ? $result : new Response($result);
            }
        }

        try {
            ob_start();

            if (empty($file)) {
                $file = 'index';
            }

            if (in_array($file, ['game'])) {
                require app_path('Http') . '/' . $file . '.php';
            }

            $output = ob_get_clean();
        } catch (LegacyView $e) {
            $output = $e->getView();
        }

        return new Response($output);
    }
}
