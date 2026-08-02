<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class HighscoreRequest extends FormRequest
{
    public const WHO_PLAYER = 1;
    public const WHO_ALLIANCE = 2;

    public const TYPE_TOTAL = 1;
    public const TYPE_ECONOMY = 2;
    public const TYPE_RESEARCH = 3;
    public const TYPE_MILITARY = 4;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * No validation rules — invalid inputs are coerced to safe defaults in
     * prepareForValidation() so the page never 422s from a malformed link
     * (e.g. range=0 from a fresh user with rank 0).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        $who = (int) $this->input('who', self::WHO_PLAYER);
        $type = (int) $this->input('type', self::TYPE_TOTAL);
        $range = (int) $this->input('range', 1);

        $this->merge([
            'who' => \in_array($who, [self::WHO_PLAYER, self::WHO_ALLIANCE], true) ? $who : self::WHO_PLAYER,
            'type' => $type,
            'range' => max(1, $range),
        ]);
    }

    public function who(): int
    {
        $who = (int) $this->input('who', self::WHO_PLAYER);

        return $who === self::WHO_ALLIANCE ? self::WHO_ALLIANCE : self::WHO_PLAYER;
    }

    public function type(): int
    {
        $type = (int) $this->input('type', self::TYPE_TOTAL);

        // Legacy compat: old "5" used to mean defenses; now folded into military.
        if ($type === 5) {
            return self::TYPE_MILITARY;
        }

        return in_array($type, [1, 2, 3, 4], true) ? $type : self::TYPE_TOTAL;
    }

    public function range(): int
    {
        return max(1, (int) $this->input('range', 1));
    }
}
