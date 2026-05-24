<?php

declare(strict_types=1);

namespace Tests\Unit\Legacy\App\Http\Controllers\Game;

use App\Services\FormatService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Xgp\App\Http\Controllers\Game\HighscoreController;

#[CoversClass(HighscoreController::class)]
class HighscoreControllerTest extends TestCase
{
    public function testRankingTypeUsesOgameCategoryMapping(): void
    {
        $controller = new HighscoreController(new FormatService());

        $this->assertSame([
            'order' => 'total_points',
            'points' => 'total_points',
            'rank' => 'total_rank',
            'oldrank' => 'total_old_rank',
        ], $this->rankingType($controller, 1));

        $this->assertSame([
            'order' => 'buildings_points',
            'points' => 'buildings_points',
            'rank' => 'buildings_rank',
            'oldrank' => 'buildings_old_rank',
        ], $this->rankingType($controller, 2));

        $this->assertSame([
            'order' => 'technology_points',
            'points' => 'technology_points',
            'rank' => 'technology_rank',
            'oldrank' => 'technology_old_rank',
        ], $this->rankingType($controller, 3));

        $this->assertSame([
            'order' => 'military_points',
            'points' => 'military_points',
            'rank' => 'military_rank',
            'oldrank' => 'military_old_rank',
        ], $this->rankingType($controller, 4));
    }

    /**
     * @return array<string, string>
     */
    private function rankingType(HighscoreController $controller, int $type): array
    {
        $method = new ReflectionMethod(HighscoreController::class, 'ranking_type');

        /** @var array<string, string> $result */
        $result = $method->invoke($controller, $type);

        return $result;
    }
}
