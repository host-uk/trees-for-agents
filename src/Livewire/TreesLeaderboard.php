<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Livewire;

use HostUK\TreesForAgents\Models\TreePlanting;
use HostUK\TreesForAgents\Models\TreePlantingStats;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Trees for Agents public leaderboard component.
 *
 * Displays stats about trees planted through the Trees for Agents programme,
 * including provider leaderboard, model breakdown, and programme information.
 *
 * @see https://github.com/host-uk/trees-for-agents
 * @licence EUPL-1.2
 */
class TreesLeaderboard extends Component
{
    /**
     * The site name to display.
     */
    public string $siteName = 'Your Site';

    /**
     * The referral URL base.
     */
    public string $referralBase = '';

    public function mount(string $siteName = 'Your Site', ?string $referralBase = null): void
    {
        $this->siteName = $siteName;
        $this->referralBase = $referralBase ?? url('/ref');
    }

    public function render()
    {
        return view('trees-for-agents::livewire.trees-leaderboard', [
            'stats' => $this->getGlobalStats(),
            'leaderboard' => $this->getProviderLeaderboard(),
            'modelStats' => $this->getModelStats(),
        ]);
    }

    /**
     * Get global tree planting statistics.
     */
    protected function getGlobalStats(): array
    {
        // Check if the models exist (for demo mode without database)
        if (! class_exists(TreePlanting::class)) {
            return $this->getDemoStats();
        }

        try {
            $baseQuery = TreePlanting::whereIn('status', [
                TreePlanting::STATUS_CONFIRMED,
                TreePlanting::STATUS_PLANTED,
            ]);

            $totalTrees = (int) (clone $baseQuery)->sum('trees');
            $totalReferrals = (int) TreePlantingStats::query()->sum('total_referrals');

            return [
                'total_trees' => $totalTrees,
                'trees_this_month' => (int) (clone $baseQuery)->thisMonth()->sum('trees'),
                'trees_this_year' => (int) (clone $baseQuery)->thisYear()->sum('trees'),
                'total_referrals' => $totalReferrals,
                'queued_trees' => (int) TreePlanting::queued()->sum('trees'),
            ];
        } catch (\Throwable) {
            return $this->getDemoStats();
        }
    }

    /**
     * Get demo statistics for display without database.
     */
    protected function getDemoStats(): array
    {
        return [
            'total_trees' => 1247,
            'trees_this_month' => 89,
            'trees_this_year' => 847,
            'total_referrals' => 3421,
            'queued_trees' => 23,
        ];
    }

    /**
     * Get provider leaderboard sorted by trees planted.
     */
    protected function getProviderLeaderboard(): Collection
    {
        if (! class_exists(TreePlanting::class)) {
            return $this->getDemoLeaderboard();
        }

        try {
            return TreePlanting::query()
                ->selectRaw('provider, COUNT(DISTINCT user_id) as signups, SUM(trees) as trees')
                ->whereIn('status', [TreePlanting::STATUS_CONFIRMED, TreePlanting::STATUS_PLANTED])
                ->whereNotNull('provider')
                ->groupBy('provider')
                ->orderByDesc('trees')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'provider' => $item->provider,
                        'display_name' => $this->getProviderDisplayName($item->provider),
                        'trees' => (int) $item->trees,
                        'signups' => (int) $item->signups,
                    ];
                });
        } catch (\Throwable) {
            return $this->getDemoLeaderboard();
        }
    }

    /**
     * Get demo leaderboard for display without database.
     */
    protected function getDemoLeaderboard(): Collection
    {
        return collect([
            ['provider' => 'anthropic', 'display_name' => 'Anthropic', 'trees' => 523, 'signups' => 89],
            ['provider' => 'openai', 'display_name' => 'OpenAI', 'trees' => 412, 'signups' => 67],
            ['provider' => 'google', 'display_name' => 'Google', 'trees' => 198, 'signups' => 34],
            ['provider' => 'meta', 'display_name' => 'Meta', 'trees' => 87, 'signups' => 12],
            ['provider' => 'mistral', 'display_name' => 'Mistral', 'trees' => 27, 'signups' => 5],
        ]);
    }

    /**
     * Get model breakdown stats across all providers.
     */
    protected function getModelStats(): Collection
    {
        if (! class_exists(TreePlanting::class)) {
            return $this->getDemoModelStats();
        }

        try {
            return TreePlanting::query()
                ->selectRaw('provider, model, SUM(trees) as trees')
                ->whereIn('status', [TreePlanting::STATUS_CONFIRMED, TreePlanting::STATUS_PLANTED])
                ->whereNotNull('model')
                ->groupBy('provider', 'model')
                ->orderByDesc('trees')
                ->limit(12)
                ->get()
                ->map(function ($item) {
                    return [
                        'provider' => $item->provider,
                        'model' => $item->model,
                        'display_name' => $this->getModelDisplayName($item->model),
                        'trees' => (int) $item->trees,
                    ];
                });
        } catch (\Throwable) {
            return $this->getDemoModelStats();
        }
    }

    /**
     * Get demo model stats for display without database.
     */
    protected function getDemoModelStats(): Collection
    {
        return collect([
            ['provider' => 'anthropic', 'model' => 'claude-opus', 'display_name' => 'Claude Opus', 'trees' => 287],
            ['provider' => 'anthropic', 'model' => 'claude-sonnet', 'display_name' => 'Claude Sonnet', 'trees' => 156],
            ['provider' => 'openai', 'model' => 'gpt-4', 'display_name' => 'GPT-4', 'trees' => 234],
            ['provider' => 'openai', 'model' => 'o1', 'display_name' => 'o1', 'trees' => 98],
            ['provider' => 'google', 'model' => 'gemini-pro', 'display_name' => 'Gemini Pro', 'trees' => 123],
            ['provider' => 'anthropic', 'model' => 'claude-haiku', 'display_name' => 'Claude Haiku', 'trees' => 80],
            ['provider' => 'openai', 'model' => 'gpt-3.5', 'display_name' => 'GPT-3.5', 'trees' => 80],
            ['provider' => 'google', 'model' => 'gemini-flash', 'display_name' => 'Gemini Flash', 'trees' => 75],
        ]);
    }

    /**
     * Get display name for a provider.
     */
    protected function getProviderDisplayName(string $provider): string
    {
        return match ($provider) {
            'anthropic' => 'Anthropic',
            'openai' => 'OpenAI',
            'google' => 'Google',
            'meta' => 'Meta',
            'mistral' => 'Mistral',
            'local' => 'Local Models',
            'unknown' => 'Unknown Agents',
            default => ucfirst($provider),
        };
    }

    /**
     * Get display name for a model.
     */
    protected function getModelDisplayName(string $model): string
    {
        return match (strtolower($model)) {
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
            default => $model,
        };
    }
}
