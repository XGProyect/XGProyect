<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game;

use App\Services\Game\HomePlanetService;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;

#[CoversClass(HomePlanetService::class)]
class HomePlanetServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('home_planet_id')->default(0);
            $table->unsignedInteger('current_planet')->default(0);
            $table->unsignedInteger('galaxy')->default(0);
            $table->unsignedInteger('system')->default(0);
            $table->unsignedInteger('planet')->default(0);
        });

        Schema::connection('sqlite')->create('planets', function (Blueprint $table): void {
            $table->unsignedInteger('planet_id')->primary();
            $table->unsignedInteger('planet_user_id');
            $table->unsignedInteger('planet_galaxy')->default(0);
            $table->unsignedInteger('planet_system')->default(0);
            $table->unsignedInteger('planet_planet')->default(0);
            $table->unsignedTinyInteger('planet_type')->default(PlanetTypesEnumerator::PLANET);
            $table->unsignedInteger('planet_destroyed')->default(0);
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('planets');
        Schema::connection('sqlite')->dropIfExists('users');

        parent::tearDown();
    }

    public function testAbandoningAColonyKeepsTheExistingHomePlanet(): void
    {
        $this->seedUser(homePlanetId: 20, currentPlanetId: 30, galaxy: 2, system: 1, position: 1);
        $this->seedPlanet(planetId: 10, galaxy: 1, system: 1, position: 1);
        $this->seedPlanet(planetId: 20, galaxy: 2, system: 1, position: 1);
        $this->seedPlanet(planetId: 30, galaxy: 3, system: 1, position: 1, destroyed: 1);

        $result = (new HomePlanetService())->moveCurrentAfterAbandoningPlanet(
            userId: 1,
            abandonedPlanetId: 30,
            homePlanetId: 20
        );

        $user = DB::table('users')->where('id', 1)->first();

        $this->assertNotNull($user);
        $this->assertSame(20, $result);
        $this->assertSame(20, (int) $user->home_planet_id);
        $this->assertSame(20, (int) $user->current_planet);
    }

    public function testAbandoningTheHomePlanetPromotesTheNextActivePlanet(): void
    {
        $this->seedUser(homePlanetId: 20, currentPlanetId: 20, galaxy: 2, system: 1, position: 1);
        $this->seedPlanet(planetId: 10, galaxy: 1, system: 1, position: 1);
        $this->seedPlanet(planetId: 20, galaxy: 2, system: 1, position: 1, destroyed: 1);
        $this->seedPlanet(planetId: 30, galaxy: 3, system: 1, position: 1);

        $result = (new HomePlanetService())->moveCurrentAfterAbandoningPlanet(
            userId: 1,
            abandonedPlanetId: 20,
            homePlanetId: 20
        );

        $user = DB::table('users')->where('id', 1)->first();

        $this->assertNotNull($user);
        $this->assertSame(10, $result);
        $this->assertSame(10, (int) $user->home_planet_id);
        $this->assertSame(10, (int) $user->current_planet);
        $this->assertSame(1, (int) $user->galaxy);
        $this->assertSame(1, (int) $user->system);
        $this->assertSame(1, (int) $user->planet);
    }

    public function testLoginResetRepairsDriftedHomePlanetWhenRegisteredHomeIsActive(): void
    {
        $this->seedUser(homePlanetId: 30, currentPlanetId: 30, galaxy: 2, system: 1, position: 1);
        $this->seedPlanet(planetId: 20, galaxy: 2, system: 1, position: 1);
        $this->seedPlanet(planetId: 30, galaxy: 3, system: 1, position: 1);

        $user = User::findOrFail(1);

        $result = (new HomePlanetService())->resetCurrentToHome($user);

        $storedUser = DB::table('users')->where('id', 1)->first();

        $this->assertNotNull($storedUser);
        $this->assertSame(20, $result);
        $this->assertSame(20, (int) $storedUser->home_planet_id);
        $this->assertSame(20, (int) $storedUser->current_planet);
    }

    private function seedUser(
        int $homePlanetId,
        int $currentPlanetId,
        int $galaxy = 0,
        int $system = 0,
        int $position = 0,
    ): void {
        DB::table('users')->insert([
            'id' => 1,
            'home_planet_id' => $homePlanetId,
            'current_planet' => $currentPlanetId,
            'galaxy' => $galaxy,
            'system' => $system,
            'planet' => $position,
        ]);
    }

    private function seedPlanet(int $planetId, int $galaxy, int $system, int $position, int $destroyed = 0): void
    {
        DB::table('planets')->insert([
            'planet_id' => $planetId,
            'planet_user_id' => 1,
            'planet_galaxy' => $galaxy,
            'planet_system' => $system,
            'planet_planet' => $position,
            'planet_type' => PlanetTypesEnumerator::PLANET,
            'planet_destroyed' => $destroyed,
        ]);
    }
}
