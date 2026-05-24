<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Game;

use App\Core\GameObjects\GameObjectRegistry;
use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\FleetsService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\Game\Formulas\ProductionService;
use App\Services\Game\TechnologyInfoService;
use App\Services\SettingsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;
use Tests\TestCase;

#[CoversClass(TechnologyInfoService::class)]
class TechnologyInfoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => 'xgp_',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('current_planet')->default(0);
        });

        Schema::connection('sqlite')->create('planets', function (Blueprint $table): void {
            $table->unsignedInteger('planet_id')->primary();
            $table->unsignedInteger('planet_user_id');
            $table->unsignedTinyInteger('planet_type');
            $table->unsignedInteger('planet_last_jump_time')->default(0);
        });

        Schema::connection('sqlite')->create('buildings', function (Blueprint $table): void {
            $table->increments('building_id');
            $table->unsignedInteger('building_planet_id');
            $table->unsignedTinyInteger('building_jump_gate')->default(0);
        });

        Schema::connection('sqlite')->create('ships', function (Blueprint $table): void {
            $table->increments('ship_id');
            $table->unsignedInteger('ship_planet_id');
            $table->unsignedInteger('ship_small_cargo_ship')->default(0);
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('ships');
        Schema::connection('sqlite')->dropIfExists('buildings');
        Schema::connection('sqlite')->dropIfExists('planets');
        Schema::connection('sqlite')->dropIfExists('users');

        parent::tearDown();
    }

    public function testHandleJumpGateRejectsMoonOutsideCurrentUsersDestinations(): void
    {
        $this->seedOriginMoon();
        $this->seedTargetMoon(planetId: 99, userId: 2);

        $result = $this->buildService()->handleJumpGate(
            43,
            99,
            [202 => 1],
            $this->userData(),
            $this->originPlanetData(),
        );

        $this->assertSame('red', $result['color']);
        $this->assertSame(__('game/technologydetails.in_jump_gate_doesnt_have_one'), $result['message']);
        $this->assertSame(5, DB::table('ships')->where('ship_planet_id', 10)->value('ship_small_cargo_ship'));
        $this->assertSame(0, DB::table('ships')->where('ship_planet_id', 99)->value('ship_small_cargo_ship'));
    }

    public function testFindJumpGateDestinationReturnsOwnedReadyMoon(): void
    {
        $this->seedOriginMoon();
        $this->seedTargetMoon(planetId: 11, userId: 1);

        $service = $this->buildService();
        $initializeContext = new ReflectionMethod(TechnologyInfoService::class, 'initializeContext');
        $initializeContext->invoke($service, 43, $this->userData(), $this->originPlanetData());

        $method = new ReflectionMethod(TechnologyInfoService::class, 'findJumpGateDestination');

        $result = $method->invoke($service, 11);

        $this->assertIsArray($result);
        $this->assertSame(11, $result['planet_id']);
        $this->assertSame(1, $result['building_jump_gate']);
    }

    private function buildService(): TechnologyInfoService
    {
        return new TechnologyInfoService(
            registry: new GameObjectRegistry(),
            productionService: $this->createStub(ProductionService::class),
            formatService: new FormatService(),
            officerService: $this->createStub(OfficerService::class),
            developmentsService: $this->createStub(DevelopmentsService::class),
            fleetsService: $this->createStub(FleetsService::class),
            settings: $this->createStub(SettingsService::class),
        );
    }

    private function seedOriginMoon(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'current_planet' => 10,
        ]);

        DB::table('planets')->insert([
            'planet_id' => 10,
            'planet_user_id' => 1,
            'planet_type' => 3,
            'planet_last_jump_time' => 0,
        ]);

        DB::table('buildings')->insert([
            'building_planet_id' => 10,
            'building_jump_gate' => 1,
        ]);

        DB::table('ships')->insert([
            'ship_planet_id' => 10,
            'ship_small_cargo_ship' => 5,
        ]);
    }

    private function seedTargetMoon(int $planetId, int $userId): void
    {
        DB::table('planets')->insert([
            'planet_id' => $planetId,
            'planet_user_id' => $userId,
            'planet_type' => 3,
            'planet_last_jump_time' => 0,
        ]);

        DB::table('buildings')->insert([
            'building_planet_id' => $planetId,
            'building_jump_gate' => 1,
        ]);

        DB::table('ships')->insert([
            'ship_planet_id' => $planetId,
            'ship_small_cargo_ship' => 0,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function userData(): array
    {
        return ['id' => 1];
    }

    /**
     * @return array<string, int>
     */
    private function originPlanetData(): array
    {
        return [
            'planet_id' => 10,
            'planet_last_jump_time' => 0,
            'building_jump_gate' => 1,
            'ship_small_cargo_ship' => 5,
        ];
    }
}
