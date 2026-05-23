<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Game\QueueSequenceItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $planet_id
 * @property int $position
 * @property int $tech_id
 * @property int $target_level
 * @property int $duration
 * @property int $end_time
 */
class ResearchQueue extends Model implements QueueSequenceItem
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'planet_id',
        'position',
        'tech_id',
        'target_level',
        'duration',
        'end_time',
    ];

    protected $casts = [
        'user_id' => 'int',
        'planet_id' => 'int',
        'position' => 'int',
        'tech_id' => 'int',
        'target_level' => 'int',
        'duration' => 'int',
        'end_time' => 'int',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Planets, $this> */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planets::class, 'planet_id', 'planet_id');
    }

    public function getQueuePosition(): int
    {
        return $this->position;
    }

    public function setQueuePosition(int $position): void
    {
        $this->position = $position;
    }

    public function getQueueDuration(): int
    {
        return $this->duration;
    }

    public function setQueueDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getQueueEndTime(): int
    {
        return $this->end_time;
    }

    public function setQueueEndTime(int $endTime): void
    {
        $this->end_time = $endTime;
    }

    public function removeFromQueue(): void
    {
        $this->delete();
    }

    public function persistQueueChanges(): void
    {
        $this->save();
    }
}
