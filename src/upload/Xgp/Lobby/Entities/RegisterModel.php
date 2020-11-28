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
 * @since      Version 4.0.0
 */
namespace Xgp\Lobby\Models;

use App\Entities\Coordinates;
use App\Entities\User;
use App\Models\BaseModel;
use App\Models\PlanetModel;
use App\Models\UserModel;

/**
 * Register model
 */
class Register extends BaseModel
{
    /**
     * Create a new user
     *
     * @param array $new_user_data
     * @param array $coords
     * @return void
     */
    public function createNewUser(array $new_user_data, Coordinates $coords): int
    {
        $this->db->transStart();

        $newUserId = (new UserModel)->create($new_user_data);

        // create a new planet
        (new PlanetModel)->create($newUserId, $coords);

        $this->db->transComplete();

        return ($this->db->transStatus() === true ? $newUserId : 0);
    }
}
