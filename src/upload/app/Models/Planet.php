<?php
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
 * @since      Version 4.0.0
 */
namespace App\Models;

use App\Models\Building;
use App\Models\Defense;
use App\Models\Ship;
use Illuminate\Database\Eloquent\Model;

class Planet extends Model
{
    protected $table = 'planets';
    protected $primaryKey = 'planet_id';
    public $timestamps = false;
    protected $fillable = [
        'planet_name',
        'planet_user_id',
        'planet_galaxy',
        'planet_system',
        'planet_planet',
        'planet_last_update',
        'planet_type',
        'planet_destroyed',
        'planet_b_building',
        'planet_b_building_id',
        'planet_b_tech',
        'planet_b_tech_id',
        'planet_b_hangar',
        'planet_b_hangar_id',
        'planet_image',
        'planet_diameter',
        'planet_field_current',
        'planet_field_max',
        'planet_temp_min',
        'planet_temp_max',
        'planet_metal',
        'planet_metal_perhour',
        'planet_crystal',
        'planet_crystal_perhour',
        'planet_deuterium',
        'planet_deuterium_perhour',
        'planet_energy_used',
        'planet_energy_max',
        'planet_building_metal_mine_percent',
        'planet_building_crystal_mine_percent',
        'planet_building_deuterium_sintetizer_percent',
        'planet_building_solar_plant_percent',
        'planet_building_fusion_reactor_percent',
        'planet_ship_solar_satellite_percent',
        'planet_last_jump_time',
        'planet_debris_metal',
        'planet_debris_crystal',
        'planet_invisible_start_time',
    ];

    /**
     * Get the buildings associated to the planet
     *
     * @return void
     */
    public function buildings()
    {
        return $this->hasOne('App\Models\Building');
    }

    /**
     * Get the defenses associated to the planet
     *
     * @return void
     */
    public function defenses()
    {
        return $this->hasOne('App\Models\Defense');
    }

    /**
     * Get the ships associated to the planet
     *
     * @return void
     */
    public function ships()
    {
        return $this->hasOne('App\Models\Ship');
    }

    /**
     * Create a new planet
     *
     * @param array $data
     * @return integer
     */
    public function insert(int $userId, Coordinates $coords): int
    {
        $newPlanetId = 0;

        try {
            $newPlanetId = DB::transaction(function ($userId, $coords) {
                $newPlanetId = $this->create([
                    'planet_user_id' => $userId,
                    'planet_galaxy' => $coords->galaxy,
                    'planet_system' => $coords->system,
                    'planet_planet' => $coords->planet,
                ])->planet_id;

                (new Building)->create(['building_planet_id' => $newPlanetId]);
                (new Defense)->create(['defense_planet_id' => $newPlanetId]);
                (new Ship)->create(['ship_planet_id' => $newPlanetId]);

                return $newPlanetId;
            });
        } catch (\Exception $e) {
            return $newPlanetId;
        }

        return $newPlanetId;
    }

    /**
     * Check if the position for the provided coords is free. Returns true if it is free.
     *
     * @param Coordinates $coords
     * @return boolean
     */
    public function isPositionAvailable(Coordinates $coords): bool
    {
        $planet = $this->db->table($this->prefix(PLANETS))
            ->select('planet_id')
            ->where([
                'planet_galaxy' => $coords->galaxy,
                'planet_system' => $coords->system,
                'planet_planet' => $coords->planet,
            ])
            ->get()
            ->getRow();

        return !isset($planet->planet_id);
    }
}
