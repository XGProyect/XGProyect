<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use UnexpectedValueException;

class SessionService
{
    public function setLoginData(int | string | null $userId, string $password): void
    {
        $secretWord = config('SECRETWORD');

        if (!is_string($secretWord)) {
            throw new UnexpectedValueException('SECRETWORD must be a string.');
        }

        session([
            'user_id' => $userId,
            'user_password' => Hash::make(
                ($password . '-' . $secretWord)
            ),
        ]);
    }
}
