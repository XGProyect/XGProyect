<?php

namespace Xgp\App\Libraries;

use Xgp\App\Core\Database;
use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Libraries\Messenger\MessagesFormat;
use Xgp\App\Libraries\Messenger\MessagesOptions;
use Xgp\App\Libraries\Messenger\Messenger;

abstract class Functions
{
    /**
     * Return a new instance of Template
     *
     * @return Template
     */
    public static function getTemplate(): Template
    {
        return new Template();
    }

    /**
     * chronoApplet
     *
     * @param string  $type  Type
     * @param string  $ref   Ref
     * @param string  $value Value
     * @param boolean $init  Init
     *
     * @return string
     */
    public static function chronoApplet($type, $ref, $value, $init)
    {
        if ($init == true) {
            $template = 'general/chrono_applet_init';
        } else {
            $template = 'general/chrono_applet';
        }

        $parse['type'] = $type;
        $parse['ref'] = $ref;
        $parse['value'] = $value;

        return Template::getInstance()->render(
            $template,
            $parse
        );
    }

    public static function readConfig(?string $name, $all = false): mixed
    {
        $configs = Options::getInstance();

        if ($all) {
            $return = [];

            foreach ($configs->getOptions(null) as $row) {
                $return[$row['option_name']] = $row['option_value'];
            }

            return $return;
        } else {
            return $configs->getOptions($name);
        }
    }

    public static function updateConfig($name, $value)
    {
        return Options::getInstance()->writeOptions($name, $value);
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
        return self::readConfig('fleet_speed') / 2500;
    }

    public static function message($mes, ?string $dest = null, string $time = '3', bool $topnav = false, bool $menu = true, $center = true): void
    {
        $parse['mes'] = $mes;
        $parse['dest'] = $dest;
        $parse['time'] = $time;
        $parse['middle1'] = '';
        $parse['middle2'] = '';

        if ($center) {
            $parse['middle1'] = '<div id="content">';
            $parse['middle2'] = '</div>';
        }

        Template::getInstance()->view(
            'message.view',
            $parse
        );
    }

    public static function isModuleAccesible(int $module = 0): int
    {
        $modules = self::readConfig('modules');
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

    /**
     * getDefaultVacationTime
     *
     * @return int
     */
    public static function getDefaultVacationTime()
    {
        return (time() + (3600 * 24 * VACATION_TIME_FORCED));
    }

    /**
     * setImage
     *
     * @param string $path       Image path
     * @param string $title      Title
     * @param string $attributes Attributes - css & js
     *
     * @return string
     */
    public static function setImage($path, $title = 'img', $attributes = '')
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

    public static function getCurrentLanguage(bool $installed = false): string
    {
        if ($installed) {
            return self::readConfig('lang');
        }

        // set the user language reading the config file
        if ($installed && !isset($_COOKIE['current_lang'])) {
            $_COOKIE['current_lang'] = self::readConfig('lang');
        }

        // get the language from the session
        if (isset($_COOKIE['current_lang'])) {
            return $_COOKIE['current_lang'];
        }

        return 'english'; // the universal language if nothing was set
    }

    /**
     * setCurrentLanguage
     *
     * @param string $lang Language
     *
     * @return void
     */
    public static function setCurrentLanguage($lang = '')
    {
        // force english
        if (!in_array($lang, self::getLanguagesList())) {
            $lang = 'english';
        }

        $db = new Database();

        // set the user language reading the config file
        if ($db != null && $db->testConnection() && !isset($_COOKIE['current_lang'])) {
            self::updateConfig('lang', $lang);
        }

        setcookie('current_lang', $lang);
    }

    /**
     * Get the list of available languages
     *
     * @return array
     */
    public static function getLanguagesList()
    {
        $langs_dir = opendir(lang_path());
        $exceptions = ['.', '..', '.htaccess', 'index.html', '.DS_Store'];
        $langs = [];

        while (($lang_dir = readdir($langs_dir)) !== false) {
            if (!in_array($lang_dir, $exceptions)) {
                $langs[] = $lang_dir;
            }
        }

        return $langs;
    }

    /**
     * getLanguages
     *
     * @param string $current_lang Current language
     *
     * @return string
     */
    public static function getLanguages($current_lang)
    {
        $langs_dir = opendir(lang_path());
        $exceptions = ['.', '..', '.htaccess', 'index.html', '.DS_Store'];
        $lang_options = '';

        while (($lang_dir = readdir($langs_dir)) !== false) {
            if (!in_array($lang_dir, $exceptions)) {
                $lang_options .= '<option ';

                if ($current_lang == $lang_dir) {
                    $lang_options .= 'selected = selected';
                }

                $lang_options .= ' value="' . $lang_dir . '">' . $lang_dir . '</option>';
            }
        }

        return $lang_options;
    }

    public static function messageBox(string $title, string $message, string $goto = '', string $button = ' ok ', bool $twoLines = false): void
    {
        Template::getInstance()->view(
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

    /**
     * Encrypt a password
     *
     * @param string $password
     * @return string
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Generate a random password
     *
     * @return string
     */
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
