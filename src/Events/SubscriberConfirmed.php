<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Events;

/**
 * Event dispatched when a subscriber is confirmed via webhook.
 *
 * Listen to this event to trigger additional actions when trees are planted,
 * such as notifications, analytics, or third-party integrations.
 *
 * @see https://github.com/host-uk/trees-for-agents
 * @licence EUPL-1.2
 */
class SubscriberConfirmed
{

    public function __construct(
        public readonly string $provider,
        public readonly ?string $model,
        public readonly int $trees,
        public readonly ?int $plantingId = null
    ) {}

    /**
     * Get the display name for the provider.
     */
    public function getProviderDisplayName(): string
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
     * Get a human-readable description of the event.
     */
    public function getDescription(): string
    {
        $modelPart = $this->model ? " ({$this->model})" : '';
        $treePart = $this->trees === 1 ? '1 tree' : "{$this->trees} trees";

        return "{$this->getProviderDisplayName()}{$modelPart} planted {$treePart}";
    }
}
