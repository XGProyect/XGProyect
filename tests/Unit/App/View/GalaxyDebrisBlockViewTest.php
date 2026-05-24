<?php

declare(strict_types=1);

namespace Tests\Unit\App\View;

use Tests\TestCase;

class GalaxyDebrisBlockViewTest extends TestCase
{
    public function testItRendersEscapedOverlibTooltipWithHarvestAction(): void
    {
        $html = view('galaxy.galaxy_debris_block', [
            'galaxy' => 1,
            'system' => 2,
            'planet' => 3,
            'image' => '/assets/planets/debris.jpg',
            'planettype' => 2,
            'recsended' => 4,
            'planet_debris_metal' => '1.000',
            'planet_debris_crystal' => '500',
        ])->render();

        $this->assertStringContainsString('return overlib("\u003Ctable', $html);
        $this->assertStringContainsString('href=\u0022#\u0022', $html);
        $this->assertStringContainsString('onclick=\u0022doit(8, 1, 2, 3, 2, 4); return nd();\u0022', $html);
        $this->assertStringNotContainsString('onclick=&#039javascript:doit', $html);
        $this->assertStringNotContainsString('href= #', $html);
    }
}
