<?php

declare(strict_types=1);

namespace Tests\Unit\Legacy\App\Libraries\BattleEngine;

use Tests\TestCase;
use Xgp\App\Libraries\BattleEngine\Core\Battle;
use Xgp\App\Libraries\BattleEngine\Models\Defense;
use Xgp\App\Libraries\BattleEngine\Models\Fleet;
use Xgp\App\Libraries\BattleEngine\Models\Player;
use Xgp\App\Libraries\BattleEngine\Models\PlayerGroup;
use Xgp\App\Libraries\BattleEngine\Models\Ship;
use Xgp\App\Libraries\BattleEngine\Utils\LangManager;
use Xgp\App\Libraries\BattleEngine\Utils\Math;
use Xgp\App\Libraries\BattleEngine\Utils\Number;
use Xgp\App\Libraries\Missions\AttackLang;

class BattleEngineShotCountTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 6) . '/legacy/app/Libraries/BattleEngine/Utils/Includer.php';
    }

    public function testRealShotMathReturnsIntegerWholeResult(): void
    {
        $shots = Math::multiple(new Number(1 / 22), new Number(1000), true);

        $this->assertSame(45, $shots->result);
    }

    public function testBattleUsesIntegerShotCountsForDamage(): void
    {
        mt_srand(1234);
        LangManager::getInstance()->setImplementation(new AttackLang([
            202 => 'ship_small_cargo_ship',
            205 => 'ship_heavy_fighter',
            401 => 'defense_rocket_launcher',
        ]));

        $attackers = new PlayerGroup([
            new Player(1, [
                new Fleet(2, [
                    new Ship(202, 1, [], 10, [2000, 2000], 5),
                ]),
            ], 12, 12, 12),
        ]);

        $defenders = new PlayerGroup([
            new Player(7, [
                new Fleet(0, [
                    new Defense(401, 1000, [202 => 5], 20, [2000, 0], 80),
                    new Ship(205, 1000, [202 => 3], 50, [20000, 7000], 400),
                ]),
            ], 12, 12, 12),
        ]);

        $battle = new Battle($attackers, $defenders);
        $battle->startBattle(false);
        $report = $battle->getReport();
        $reportContent = (string) $report;

        $this->assertIsInt($report->getMoonProb());
        $this->assertStringContainsString('Rocket Launcher', $reportContent);
        $this->assertStringNotContainsString('game/ships.defense_rocket_launcher', $reportContent);
        $this->assertStringNotContainsString('Repaired Defense:<br>', $reportContent);
        $this->assertMatchesRegularExpression(
            "/The Attacker fires a total of .+ with a total strength of .+\.<br>\s+The defender's shields absorb/",
            $reportContent
        );
    }
}
