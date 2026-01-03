<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Aggregated tree planting statistics model.
 *
 * Stores pre-aggregated stats for efficient leaderboard queries.
 *
 * @property int $id
 * @property string $provider
 * @property string|null $model
 * @property int $total_trees
 * @property int $total_referrals
 * @property int $total_signups
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @see https://github.com/host-uk/trees-for-agents
 * @licence EUPL-1.2
 */
class TreePlantingStats extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider',
        'model',
        'total_trees',
        'total_referrals',
        'total_signups',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_trees' => 'integer',
        'total_referrals' => 'integer',
        'total_signups' => 'integer',
    ];

    /**
     * Get or create stats for a provider/model combination.
     */
    public static function forProvider(string $provider, ?string $model = null): self
    {
        return static::firstOrCreate(
            ['provider' => $provider, 'model' => $model],
            ['total_trees' => 0, 'total_referrals' => 0, 'total_signups' => 0]
        );
    }

    /**
     * Increment tree count.
     */
    public function incrementTrees(int $count = 1): self
    {
        $this->increment('total_trees', $count);

        return $this;
    }

    /**
     * Increment referral count.
     */
    public function incrementReferrals(int $count = 1): self
    {
        $this->increment('total_referrals', $count);

        return $this;
    }

    /**
     * Increment signup count.
     */
    public function incrementSignups(int $count = 1): self
    {
        $this->increment('total_signups', $count);

        return $this;
    }
}
