<?php

declare(strict_types=1);

namespace Xgp\App\Helpers;

/**
 * @deprecated v4.0.0 use FormatService::link() or any laravel helper instead
 */
abstract class UrlHelper
{
    public static function prepUrl(string $url = ''): string
    {
        if ($url == 'http://' or $url == '') {
            return '';
        }

        if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
            $url = self::getUrlProtocol() . $url;
        }

        return $url;
    }

    /**
     * @SuppressWarnings("PHPMD.Superglobals")
     */
    public static function getUrlProtocol(): string
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' or $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    }
}
