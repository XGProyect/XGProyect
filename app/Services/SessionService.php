<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class SessionService
{
    public function setLoginData(int $userId, string $password): void
    {
        session([
            'user_id' => $userId,
            'user_password' => Hash::make(
                ($password . '-' . config('SECRETWORD'))
            ),
        ]);
    }

    public function setAdminData(int $userId, string $password): void
    {
        session([
            'admin_id' => $userId,
            'admin_password' => Hash::make(
                ($password . '-' . config('SECRETWORD'))
            ),
        ]);
    }
}
