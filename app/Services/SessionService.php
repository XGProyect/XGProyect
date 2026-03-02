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
}
