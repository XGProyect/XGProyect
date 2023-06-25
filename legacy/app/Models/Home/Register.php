<?php

declare(strict_types=1);

namespace Xgp\App\Models\Home;

use App\Models\Planets;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Throwable;
use Xgp\App\Core\Enumerators\UserRanksEnumerator;
use Xgp\App\Core\Model;
use Xgp\App\Libraries\PlanetLib;

class Register extends Model
{
    public function createNewUser(FormRequest $request, array $coords, bool $isAdmin = false): ?User
    {
        try {
            DB::beginTransaction();

            // create the new user
            $newUser = User::create(array_merge(
                $request->validated(),
                [
                    'name' => $request->validated()['username'],
                    'home_planet_id' => 0,
                    'current_planet' => 0,
                    'lastip' => $request->ip(),
                    'ip_at_reg' => $request->ip(),
                    'agent' => $request->header('User-Agent'),
                    'current_page' => $request->getRequestUri(),
                    'register_time' => time(),
                    'onlinetime' => time(),
                    'authlevel' => $isAdmin ? UserRanksEnumerator::ADMIN : UserRanksEnumerator::PLAYER,
                ]
            ));

            $newUser->preferences()->create();
            $newUser->premium()->create();
            $newUser->research()->create();
            $newUser->stats()->create();

            // create a new planet
            (new PlanetLib())->setNewPlanet($coords['galaxy'], $coords['system'], $coords['planet'], $newUser->id, '', true);

            // assign the new planet to the new user
            $this->updateUserPlanet($coords, $newUser->id);

            DB::commit();

            return $newUser;
        } catch (Throwable $e) {
            DB::rollback();

            throw $e;
        }

        return null;
    }

    public function updateUserPlanet(array $coords, int $userId): void
    {
        User::where('id', $userId)->update([
            'home_planet_id' => Planets::where('planet_user_id', $userId)->value('planet_id'),
            'current_planet' => Planets::where('planet_user_id', $userId)->value('planet_id'),
            'galaxy' => $coords['galaxy'],
            'system' => $coords['system'],
            'planet' => $coords['planet'],
        ]);
    }
}
