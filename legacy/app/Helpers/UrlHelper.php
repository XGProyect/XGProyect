<?php

declare(strict_types=1);

namespace Xgp\App\Helpers;

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

    public static function setUrl(string $hyperlink, string $text, string $title = '', string $attributes = ''): string
    {
        if (empty($hyperlink)) {
            $hyperlink = '#';
        }

        if (!empty($title)) {
            $title = 'title="' . $title . '"';
        }

        if (!empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

        return '<a href="' . $hyperlink . '" ' . $title . ' ' . $attributes . '>' . $text . '</a>';
    }

    public static function getUrlProtocol(): string
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' or $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    }
}
