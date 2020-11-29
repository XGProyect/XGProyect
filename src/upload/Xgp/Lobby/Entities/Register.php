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
namespace Xgp\Lobby\Entities;

use App\Models\Planet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Register model
 */
class Register extends Model
{
    /**
     * Create a new user
     *
     * @param array $newUserData
     * @param array $coords
     * @return void
     */
    public function createNewUser(array $newUserData, Coordinates $coords): int
    {
        $newUserId = 0;

        try {
            $newUserId = DB::transaction(function ($newUserData, $coords) {
                $newUserId = (new User)->insert($newUserData);

                // create a new planet
                (new Planet)->insert($newUserId, $coords);

                return $newUserId;
            });
        } catch (\Exception $e) {
            return $newUserId;
        }

        return $newUserId;
    }
}
