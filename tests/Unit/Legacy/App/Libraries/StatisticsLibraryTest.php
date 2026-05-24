<?php

declare(strict_types=1);

namespace Tests\Unit\Legacy\App\Libraries;

use App\Core\GameObjects\GameObjectRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Xgp\App\Libraries\StatisticsLibrary;

/** @SuppressWarnings("PHPMD.StaticAccess") */
#[CoversClass(StatisticsLibrary::class)]
class StatisticsLibraryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => 'xgp_',
            'DB_PREFIX' => 'xgp_',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTables();

        DB::table('options')->insert([
            'name' => 'stat_points',
            'value' => '1000',
            'type' => 'int',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (['defenses', 'ships', 'buildings', 'planets', 'research', 'users_statistics', 'users', 'options'] as $table) {
            Schema::connection('sqlite')->dropIfExists($table);
        }

        parent::tearDown();
    }

    public function testRebuildPointsUpdatesResearchChangedFromAdmin(): void
    {
        $this->seedUserWithStatistics(1);

        DB::table('research')->insert([
            'research_user_id' => 1,
            'research_energy_technology' => 12,
        ]);

        $statistics = new StatisticsLibrary();

        $this->assertTrue($statistics->rebuildPoints(1, 0, 'research'));
        $this->assertEqualsWithDelta(
            $this->expectedPoints(113, 12),
            $this->statValue('user_statistic_technology_points'),
            0.00001
        );
    }

    public function testRebuildPointsAggregatesPlanetCategoriesForTheWholeUser(): void
    {
        $this->seedUserWithStatistics(1);

        DB::table('planets')->insert([
            ['planet_id' => 10, 'planet_user_id' => 1, 'planet_destroyed' => 0],
            ['planet_id' => 11, 'planet_user_id' => 1, 'planet_destroyed' => 0],
            ['planet_id' => 12, 'planet_user_id' => 1, 'planet_destroyed' => time() + 3600],
        ]);

        DB::table('buildings')->insert([
            ['building_planet_id' => 10, 'building_metal_mine' => 2],
            ['building_planet_id' => 11, 'building_metal_mine' => 1],
            ['building_planet_id' => 12, 'building_metal_mine' => 10],
        ]);

        $statistics = new StatisticsLibrary();

        $this->assertTrue($statistics->rebuildPoints(1, 10, 'buildings'));
        $this->assertEqualsWithDelta(
            $this->expectedPoints(1, 2) + $this->expectedPoints(1, 1),
            $this->statValue('user_statistic_buildings_points'),
            0.00001
        );
    }

    public function testRebuildAllPointsRefreshesStaleStoredTotals(): void
    {
        $this->seedUserWithStatistics(1);

        DB::table('research')->insert([
            'research_user_id' => 1,
            'research_energy_technology' => 12,
        ]);

        DB::table('planets')->insert([
            'planet_id' => 10,
            'planet_user_id' => 1,
            'planet_destroyed' => 0,
        ]);

        DB::table('buildings')->insert([
            'building_planet_id' => 10,
            'building_metal_mine' => 2,
        ]);

        (new StatisticsLibrary())->rebuildAllPoints();

        $stats = DB::table('users_statistics')->where('user_statistic_user_id', 1)->first();

        $this->assertNotNull($stats);
        $this->assertEqualsWithDelta($this->expectedPoints(113, 12), (float) $stats->user_statistic_technology_points, 0.00001);
        $this->assertEqualsWithDelta($this->expectedPoints(1, 2), (float) $stats->user_statistic_buildings_points, 0.00001);
    }

    private function createTables(): void
    {
        Schema::connection('sqlite')->create('options', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('value');
            $table->string('type')->default('');
        });

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->unsignedTinyInteger('authlevel')->default(0);
            $table->unsignedInteger('ally_id')->default(0);
        });

        Schema::connection('sqlite')->create('users_statistics', function (Blueprint $table): void {
            $table->unsignedInteger('user_statistic_user_id')->primary();
            $table->double('user_statistic_buildings_points')->default(0);
            $table->integer('user_statistic_buildings_old_rank')->default(0);
            $table->integer('user_statistic_buildings_rank')->default(0);
            $table->double('user_statistic_defenses_points')->default(0);
            $table->integer('user_statistic_defenses_old_rank')->default(0);
            $table->integer('user_statistic_defenses_rank')->default(0);
            $table->double('user_statistic_ships_points')->default(0);
            $table->integer('user_statistic_ships_old_rank')->default(0);
            $table->integer('user_statistic_ships_rank')->default(0);
            $table->double('user_statistic_technology_points')->default(0);
            $table->integer('user_statistic_technology_old_rank')->default(0);
            $table->integer('user_statistic_technology_rank')->default(0);
            $table->double('user_statistic_total_points')->default(0);
            $table->integer('user_statistic_total_old_rank')->default(0);
            $table->integer('user_statistic_total_rank')->default(0);
            $table->integer('user_statistic_update_time')->default(0);
        });

        Schema::connection('sqlite')->create('research', function (Blueprint $table): void {
            $table->increments('research_id');
            $table->unsignedInteger('research_user_id')->unique();
            $table->integer('research_energy_technology')->default(0);
        });

        Schema::connection('sqlite')->create('planets', function (Blueprint $table): void {
            $table->unsignedInteger('planet_id')->primary();
            $table->unsignedInteger('planet_user_id');
            $table->integer('planet_destroyed')->default(0);
        });

        Schema::connection('sqlite')->create('buildings', function (Blueprint $table): void {
            $table->increments('building_id');
            $table->unsignedInteger('building_planet_id');
            $table->integer('building_metal_mine')->default(0);
        });

        Schema::connection('sqlite')->create('ships', function (Blueprint $table): void {
            $table->increments('ship_id');
            $table->unsignedInteger('ship_planet_id');
        });

        Schema::connection('sqlite')->create('defenses', function (Blueprint $table): void {
            $table->increments('defense_id');
            $table->unsignedInteger('defense_planet_id');
        });
    }

    private function seedUserWithStatistics(int $userId): void
    {
        DB::table('users')->insert([
            'id' => $userId,
            'authlevel' => 0,
            'ally_id' => 0,
        ]);

        DB::table('users_statistics')->insert([
            'user_statistic_user_id' => $userId,
        ]);
    }

    private function expectedPoints(int $objectId, int $level): float
    {
        $price = (new GameObjectRegistry())->get($objectId)->getPrice();
        $factor = $price->getFactor();
        $multiplier = ($factor === 1.0)
            ? $level
            : (pow($factor, $level) - 1) / ($factor - 1);

        return (($price->getMetal() + $price->getCrystal() + $price->getDeuterium()) * $multiplier) / 1000;
    }

    private function statValue(string $column): float
    {
        $value = DB::table('users_statistics')->where('user_statistic_user_id', 1)->value($column);

        return is_numeric($value) ? (float) $value : 0.0;
    }
}
