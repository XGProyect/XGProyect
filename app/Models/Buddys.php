<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BuddyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int     $buddy_id
 * @property int     $buddy_sender
 * @property int     $buddy_receiver
 * @property int     $buddy_status
 * @property string  $buddy_request_text
 */
class Buddys extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'buddys';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'buddy_id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'buddy_sender',
        'buddy_receiver',
        'buddy_status',
        'buddy_request_text',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'buddy_id' => 'int',
        'buddy_sender' => 'int',
        'buddy_receiver' => 'int',
        'buddy_status' => 'int',
        'buddy_request_text' => 'string',
    ];

    public $timestamps = false;

    // Scopes

    /**
     * Rows where the given user is either the sender or the receiver.
     *
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeInvolving(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId): void {
            $q->where('buddy_sender', $userId)
                ->orWhere('buddy_receiver', $userId);
        });
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('buddy_status', BuddyStatus::Accepted->value);
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('buddy_status', BuddyStatus::Pending->value);
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopeReceivedBy(Builder $query, int $userId): Builder
    {
        return $query->where('buddy_receiver', $userId);
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopeSentBy(Builder $query, int $userId): Builder
    {
        return $query->where('buddy_sender', $userId);
    }

    /**
     * Existing relation between two users in either direction, regardless of status.
     *
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeBetween(Builder $query, int $userA, int $userB): Builder
    {
        return $query->where(function (Builder $q) use ($userA, $userB): void {
            $q->where(function (Builder $sub) use ($userA, $userB): void {
                $sub->where('buddy_sender', $userA)->where('buddy_receiver', $userB);
            })->orWhere(function (Builder $sub) use ($userA, $userB): void {
                $sub->where('buddy_sender', $userB)->where('buddy_receiver', $userA);
            });
        });
    }

    // Helpers

    public function status(): BuddyStatus
    {
        return BuddyStatus::from((int) $this->buddy_status);
    }

    public function isAccepted(): bool
    {
        return $this->status() === BuddyStatus::Accepted;
    }

    public function otherUserId(int $viewerId): int
    {
        return $this->buddy_sender === $viewerId ? $this->buddy_receiver : $this->buddy_sender;
    }

    public function wasSentBy(int $userId): bool
    {
        return $this->buddy_sender === $userId;
    }

    // Relations

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buddy_sender');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buddy_receiver');
    }
}
