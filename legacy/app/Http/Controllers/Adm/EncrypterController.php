<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;

class EncrypterController extends BaseController
{
    private string $unencrypted = '';
    private string $encrypted = '';
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->runAction();

        Template::legacyView(
            'admin.encrypter',
            [
                'unencrypted' => $this->unencrypted ?? '',
                'encrypted' => $this->encrypted ?? '',
            ]
        );
    }

    private function runAction(): void
    {
        $unencrypted = filter_input(INPUT_POST, 'unencrypted');

        if ($unencrypted) {
            $this->unencrypted = $unencrypted;
            $this->encrypted = Functions::hash($unencrypted);
        }
    }
}
