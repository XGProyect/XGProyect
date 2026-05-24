<?php

declare(strict_types=1);

namespace Tests\Unit\Legacy\App\Libraries;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;
use Xgp\App\Libraries\GalaxyLib;

class GalaxyLibTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('DPATH')) {
            define('DPATH', 'assets/upload/skins/xgproyect/');
        }
    }

    public function testActionsBlockSpyIconCancelsDefaultAnchorNavigation(): void
    {
        $galaxyLib = $this->makeGalaxyLib();

        $this->setPrivateProperty($galaxyLib, 'row_data', [
            'id' => 2,
            'authlevel' => 0,
            'preference_vacation_mode' => 0,
            'planet_galaxy' => 1,
        ]);

        $method = new ReflectionMethod(GalaxyLib::class, 'actionsBlock');

        $html = $method->invoke($galaxyLib);

        $this->assertStringContainsString(
            'onclick="javascript:doit(6, 1, 2, 3, 1, 4); return false;"',
            $html
        );
    }

    public function testPopupSpyLinkCancelsDefaultAnchorNavigation(): void
    {
        $galaxyLib = $this->makeGalaxyLib();

        $method = new ReflectionMethod(GalaxyLib::class, 'spyLink');

        $html = $method->invoke($galaxyLib, GalaxyLib::MOON_TYPE);

        $this->assertStringContainsString(
            "onclick=\\'javascript:doit(6, 1, 2, 3, 3, 4); return false;\\'",
            $html
        );
    }

    private function makeGalaxyLib(): GalaxyLib
    {
        $reflectionClass = new ReflectionClass(GalaxyLib::class);
        $galaxyLib = $reflectionClass->newInstanceWithoutConstructor();

        $this->setPrivateProperty($galaxyLib, 'current_user', [
            'id' => 1,
            'ally_id' => 0,
            'current_planet' => 10,
            'preference_spy_probes' => 4,
            'research_impulse_drive' => 0,
        ]);
        $this->setPrivateProperty($galaxyLib, 'current_planet', [
            'defense_interplanetary_missile' => 0,
            'planet_galaxy' => 1,
        ]);
        $this->setPrivateProperty($galaxyLib, 'galaxy', 1);
        $this->setPrivateProperty($galaxyLib, 'system', 2);
        $this->setPrivateProperty($galaxyLib, 'planet', 3);

        return $galaxyLib;
    }

    private function setPrivateProperty(GalaxyLib $galaxyLib, string $property, mixed $value): void
    {
        $reflectionProperty = new ReflectionProperty(GalaxyLib::class, $property);
        $reflectionProperty->setValue($galaxyLib, $value);
    }
}
