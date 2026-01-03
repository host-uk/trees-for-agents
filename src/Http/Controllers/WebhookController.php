<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Http\Controllers;

use HostUK\TreesForAgents\Events\SubscriberConfirmed;
use HostUK\TreesForAgents\Models\TreePlanting;
use HostUK\TreesForAgents\Services\AgentDetection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Webhook controller for receiving subscriber notifications.
 *
 * External systems can notify Trees for Agents when a subscriber converts,
 * triggering tree planting and leaderboard updates.
 *
 * @see https://github.com/host-uk/trees-for-agents
 * @licence EUPL-1.2
 */
class WebhookController extends Controller
{
    public function __construct(
        protected AgentDetection $agentDetection
    ) {}

    /**
     * Handle incoming subscriber webhook.
     *
     * POST /api/trees/webhooks/subscriber
     *
     * Expected payload:
     * {
     *   "event": "subscriber.confirmed",
     *   "provider": "anthropic",
     *   "model": "claude-opus",
     *   "user_id": "optional-user-identifier",
     *   "trees": 1,
     *   "metadata": {}
     * }
     */
    public function subscriber(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => 'required|string|in:subscriber.confirmed,subscriber.upgraded',
            'provider' => 'required|string|max:50',
            'model' => 'nullable|string|max:100',
            'user_id' => 'nullable|string|max:255',
            'trees' => 'integer|min:1|max:100',
            'metadata' => 'nullable|array',
        ]);

        // Validate provider is in our known list
        if (! $this->agentDetection->isValidProvider($validated['provider'])) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid provider',
                'valid_providers' => $this->agentDetection->getValidProviders(),
            ], 422);
        }

        $trees = $validated['trees'] ?? 1;
        $event = $validated['event'];

        try {
            // Record the tree planting
            $planting = $this->recordTreePlanting(
                provider: $validated['provider'],
                model: $validated['model'] ?? null,
                userId: $validated['user_id'] ?? null,
                trees: $trees,
                event: $event,
                metadata: $validated['metadata'] ?? []
            );

            // Dispatch event for listeners
            event(new SubscriberConfirmed(
                provider: $validated['provider'],
                model: $validated['model'] ?? null,
                trees: $trees,
                plantingId: $planting->id ?? null
            ));

            Log::info('Trees for Agents: Subscriber webhook processed', [
                'provider' => $validated['provider'],
                'model' => $validated['model'] ?? 'unknown',
                'trees' => $trees,
                'event' => $event,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Recorded {$trees} tree(s) for {$validated['provider']}",
                'planting_id' => $planting->id ?? null,
                'total_trees' => $this->getProviderTotalTrees($validated['provider']),
            ]);
        } catch (\Throwable $e) {
            Log::error('Trees for Agents: Webhook processing failed', [
                'error' => $e->getMessage(),
                'provider' => $validated['provider'],
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process webhook',
            ], 500);
        }
    }

    /**
     * Record a tree planting in the database.
     */
    protected function recordTreePlanting(
        string $provider,
        ?string $model,
        ?string $userId,
        int $trees,
        string $event,
        array $metadata
    ): ?TreePlanting {
        try {
            return TreePlanting::create([
                'provider' => $provider,
                'model' => $model,
                'user_id' => $userId,
                'trees' => $trees,
                'source' => 'webhook',
                'event' => $event,
                'status' => TreePlanting::STATUS_CONFIRMED,
                'metadata' => $metadata,
                'confirmed_at' => now(),
            ]);
        } catch (\Throwable) {
            // Table may not exist (demo mode or migrations not run)
            return null;
        }
    }

    /**
     * Get total trees planted by a provider.
     */
    protected function getProviderTotalTrees(string $provider): int
    {
        try {
            return (int) TreePlanting::query()
                ->where('provider', $provider)
                ->whereIn('status', [
                    TreePlanting::STATUS_CONFIRMED,
                    TreePlanting::STATUS_PLANTED,
                ])
                ->sum('trees');
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Health check endpoint for the webhook.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'trees-for-agents-webhook',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
