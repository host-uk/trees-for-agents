<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tree planting record model.
 *
 * Records each tree planting event from agent referrals.
 *
 * @property int $id
 * @property string $provider
 * @property string|null $model
 * @property string|null $user_id
 * @property int $trees
 * @property string $source
 * @property string|null $event
 * @property string $status
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $confirmed_at
 * @property \Carbon\Carbon|null $planted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @see https://github.com/host-uk/trees-for-agents
 * @licence EUPL-1.2
 */
class TreePlanting extends Model
{
    use HasFactory;

    /**
     * Status constants.
     */
    public const STATUS_QUEUED = 'queued';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PLANTED = 'planted';
    public const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider',
        'model',
        'user_id',
        'trees',
        'source',
        'event',
        'status',
        'metadata',
        'confirmed_at',
        'planted_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'trees' => 'integer',
        'metadata' => 'array',
        'confirmed_at' => 'datetime',
        'planted_at' => 'datetime',
    ];

    /**
     * Scope to queued plantings.
     */
    public function scopeQueued(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_QUEUED);
    }

    /**
     * Scope to confirmed plantings.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to planted plantings.
     */
    public function scopePlanted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PLANTED);
    }

    /**
     * Scope to this month's plantings.
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Scope to this year's plantings.
     */
    public function scopeThisYear(Builder $query): Builder
    {
        return $query->whereYear('created_at', now()->year);
    }

    /**
     * Scope to a specific provider.
     */
    public function scopeForProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    /**
     * Get the display name for the provider.
     */
    public function getProviderDisplayNameAttribute(): string
    {
        return match ($this->provider) {
            'anthropic' => 'Anthropic',
            'openai' => 'OpenAI',
            'google' => 'Google',
            'meta' => 'Meta',
            'mistral' => 'Mistral',
            'local' => 'Local Model',
            'unknown' => 'Unknown Agent',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Get the display name for the model.
     */
    public function getModelDisplayNameAttribute(): ?string
    {
        if (! $this->model) {
            return null;
        }

        return match (strtolower($this->model)) {
            'claude-opus', 'claude-opus-4' => 'Claude Opus',
            'claude-sonnet', 'claude-sonnet-4' => 'Claude Sonnet',
            'claude-haiku', 'claude-haiku-3' => 'Claude Haiku',
            'gpt-4', 'gpt-4o', 'gpt-4-turbo' => 'GPT-4',
            'gpt-3.5', 'gpt-3.5-turbo' => 'GPT-3.5',
            'o1', 'o1-preview', 'o1-mini' => 'o1',
            'gemini-pro', 'gemini-1.5-pro' => 'Gemini Pro',
            'gemini-ultra', 'gemini-1.5-ultra' => 'Gemini Ultra',
            'gemini-flash', 'gemini-1.5-flash' => 'Gemini Flash',
            'llama-3', 'llama-3.1', 'llama-3.2' => 'LLaMA 3',
            default => $this->model,
        };
    }

    /**
     * Mark the planting as confirmed.
     */
    public function markConfirmed(): self
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark the planting as planted.
     */
    public function markPlanted(): self
    {
        $this->update([
            'status' => self::STATUS_PLANTED,
            'planted_at' => now(),
        ]);

        return $this;
    }
}
