<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Libraries\Messenger\MessagesFormat;
use Xgp\App\Libraries\Messenger\MessagesOptions;
use Xgp\App\Libraries\Messenger\Messenger;

abstract class Functions
{
    public static function chronoApplet(string $type, string $ref, int $value, bool $init): string
    {
        if ($init == true) {
            $template = 'scripts.chrono_applet_init';
        } else {
            $template = 'scripts.chrono_applet';
        }

        $parse['type'] = $type;
        $parse['ref'] = $ref;
        $parse['value'] = $value;

        return Template::render(
            $template,
            $parse
        );
    }

    public static function validEmail(string $address): bool
    {
        return (!preg_match(
            "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",
            $address
        )) ? false : true;
    }

    public static function fleetSpeedFactor(): string
    {
        return Options::getInstance()->get('fleet_speed') / 2500;
    }

    public static function message(string $mes, ?string $dest = null, string $time = '3', bool $topnav = true, bool $menu = true, $center = true): void
    {
        $middle = [
            'middle1' => '',
            'middle2' => '',
        ];

        if ($center) {
            $middle['middle1'] = '<div id="content">';
            $middle['middle2'] = '</div>';
        }

        Template::legacyView(
            'message.view',
            array_merge(
                $middle,
                [
                    'mes' => $mes,
                    'dest' => $dest,
                    'time' => $time,
                    'topnav' => $topnav === true ? null : true,
                    'menu' => $menu === true ? null : true,
                ]
            )
        );
    }

    public static function popupMessage(string $mes, ?string $dest = null, string $time = '3'): void
    {
        self::message($mes, $dest, $time, false, false, false);
    }

    public static function isModuleAccesible(int $module = 0): int
    {
        $modules = Options::getInstance()->get('modules');
        $modules = explode(';', $modules);

        return (int) $modules[$module];
    }

    public static function moduleMessage(int $accessLevel): void
    {
        if ($accessLevel == 0) {
            self::message(__('game/global.module_not_accesible'), '', '', true);
            exit;
        }
    }

    public static function sendMessage(int $to, int $sender, int $time = 0, int $type = 0, string $from = '', string $subject = '', string $message = '', bool $allowHtml = false): void
    {
        $options = new MessagesOptions();
        $options->setTo($to);
        $options->setSender($sender);
        $options->setTime($time);

        switch ($type) {
            case 0:
                $type = MessagesEnumerator::ESPIO;
                break;
            case 1:
                $type = MessagesEnumerator::COMBAT;
                break;
            case 2:
                $type = MessagesEnumerator::EXP;
                break;
            case 3:
                $type = MessagesEnumerator::ALLY;
                break;
            case 4:
                $type = MessagesEnumerator::USER;
                break;
            default:
            case 5:
                $type = MessagesEnumerator::GENERAL;
                break;
        }

        $options->setType($type);
        $options->setFrom($from);
        $options->setSubject($subject);

        if ($allowHtml) {
            $options->setMessageFormat(MessagesFormat::HTML);
        }

        $options->setMessageText($message);

        $messenger = new Messenger();
        $messenger->sendMessage($options);
    }

    public static function getDefaultVacationTime(): int
    {
        return (time() + (3600 * 24 * VACATION_TIME_FORCED));
    }

    public static function setImage(string $path, string $title = 'img', string $attributes = ''): string
    {
        if (!empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

        return '<img src="' . $path . '" title="' . $title . '" border="0"' . $attributes . '>';
    }

    public static function redirect(string $route): void
    {
        header('location:' . $route);
        exit;
    }

    public static function setLanguage(string $locale = ''): void
    {
        if (empty($locale)) {
            if (session()->has('locale')) {
                $locale = session('locale');
            } else {
                $locale = Options::getInstance()->get('lang');
            }
        }

        // force english
        if (!in_array($locale, self::getLanguagesList())) {
            $locale = 'en';
        }

        session(['locale' => $locale]);

        App::setLocale($locale);
    }

    public static function getLanguages(string $currentLang): string
    {
        $options = '';

        foreach (self::getLanguagesList() as $lang) {
            $options .= '<option ';

            if ($currentLang == $lang) {
                $options .= 'selected = selected';
            }

            $options .= ' value="' . $lang . '">' . $lang . '</option>';
        }

        return $options;
    }

    public static function getLanguagesList(): array
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => lang_path(),
        ]);

        return $disk->directories();
    }

    public static function messageBox(string $title, string $message, string $goto = '', string $button = ' ok ', bool $twoLines = false): void
    {
        Template::legacyView(
            'alliance.message_box',
            [
                'goto' => $goto,
                'title' => $title,
                'oneRow' => !$twoLines,
                'message' => $message,
                'button' => $button,
            ]
        );
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function generatePassword(): string
    {
        return StringsHelper::randomString(16);
    }

    public static function isCurrentPlanet(array $current, array $target): bool
    {
        return ($current['planet_galaxy'] == $target['planet_galaxy']
            && $current['planet_system'] == $target['planet_system']
            && $current['planet_planet'] == $target['planet_planet']
            && $current['planet_type'] == $target['planet_type']);
    }
}
