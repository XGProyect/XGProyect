<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Xgp\App\Libraries\Functions;

class SetLocaleController extends BaseController
{
    public function __invoke(string $locale): RedirectResponse
    {
        if ($locale === '' && session()->has('locale')) {
            $sessionLocale = session('locale');

            if (is_string($sessionLocale) && $sessionLocale !== '') {
                $locale = $sessionLocale;
            }
        }

        // force english
        if (!in_array($locale, Functions::getLanguagesList())) {
            $locale = 'en';
        }

        session(['locale' => $locale]);

        App::setLocale($locale);

        return redirect()->back();
    }
}
