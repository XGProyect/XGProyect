<?php declare (strict_types = 1);
/**
 * XG Proyect
 *
 * Open-source OGame Clon
 *
 * This content is released under the GPL-3.0 License
 *
 * Copyright (c) 2008-2021 XG Proyect
 *
 * @package    XG Proyect
 * @author     XG Proyect Team
 * @copyright  2008-2021 XG Proyect
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0 License
 * @link       https://github.com/XGProyect/
 * @since      4.0.0
 */
namespace App\Libraries;

/**
 * Requirements library
 */
class Requirements
{
    /**
     * Get all the requirements
     *
     * @return array
     */
    public function getRequirements(): array
    {
        return [
            'php_version' => [
                'result' => $this->minPHPVersion(),
                'severity' => 'danger',
            ],
            'mail' => [
                'result' => $this->checkFunctionEmailExists(),
                'severity' => 'warning',
            ],
            'intl' => [
                'result' => $this->checkExtensionIntl(),
                'severity' => 'warning',
            ],
            'libcurl' => [
                'result' => $this->checkExtensionLibcurl(),
                'severity' => 'warning',
            ],
            'mbstring' => [
                'result' => $this->checkExtensionMbstring(),
                'severity' => 'warning',
            ],
            'json' => [
                'result' => $this->checkExtensionJson(),
                'severity' => 'warning',
            ],
            'xml' => [
                'result' => $this->checkExtensionXml(),
                'severity' => 'warning',
            ],
            'mysqlnd' => [
                'result' => $this->checkExtensionMysqlnd(),
                'severity' => 'warning',
            ],
            'config' => [
                'result' => $this->checkWritableConfig(),
                'severity' => 'danger',
            ],
        ];
    }

    /**
     * Return all the requirements grouped
     *
     * @return array
     */
    public function getRequirementsByGroup(): array
    {
        $requirements = $this->getRequirements();

        return [
            'server' => [
                $requirements['php_version'],
            ],
            'php_functions' => [
                $requirements['mail'],
            ],
            'php_extensions' => [
                $requirements['intl'],
                $requirements['libcurl'],
                $requirements['mbstring'],
                $requirements['json'],
                $requirements['xml'],
                $requirements['mysqlnd'],
            ],
            'dir_writable' => [
                $requirements['config'],
            ],
        ];
    }

    /**
     * Check the minimum PHP version
     *
     * @return boolean
     */
    public function minPHPVersion(): bool
    {
        return !(version_compare(PHP_VERSION, '7.4.0', '<'));
    }

    /**
     * Check if the mail function exists
     *
     * @return boolean
     */
    public function checkFunctionEmailExists(): bool
    {
        return function_exists('mail');
    }

    /**
     * Check if the extension intl exists
     *
     * @return boolean
     */
    public function checkExtensionIntl(): bool
    {
        return extension_loaded('intl');
    }

    /**
     * Check if the extension intl exists
     *
     * @return boolean
     */
    public function checkExtensionLibcurl(): bool
    {
        return extension_loaded('libcurl');
    }

    /**
     * Check if the extension mbstring exists
     *
     * @return boolean
     */
    public function checkExtensionMbstring(): bool
    {
        return extension_loaded('mbstring');
    }

    /**
     * Check if the extension json exists
     *
     * @return boolean
     */
    public function checkExtensionJson(): bool
    {
        return extension_loaded('json');
    }

    /**
     * Check if the extension xml exists
     *
     * @return boolean
     */
    public function checkExtensionXml(): bool
    {
        return extension_loaded('xml');
    }

    /**
     * Check if the extension mysqlnd exists
     *
     * @return boolean
     */
    public function checkExtensionMysqlnd(): bool
    {
        return extension_loaded('mysqlnd');
    }

    /**
     * Check if the config file is writable
     *
     * @return boolean
     */
    public function checkWritableConfig(): bool
    {
        return is_writable(GAME_ROOT . 'config/');
    }
}
