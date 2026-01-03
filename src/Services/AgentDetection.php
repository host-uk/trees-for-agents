<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Services;

use HostUK\TreesForAgents\Support\AgentIdentity;
use Illuminate\Http\Request;

/**
 * Service for detecting AI agents from HTTP requests.
 *
 * Identifies AI agent providers (Anthropic, OpenAI, Google, etc.) from:
 * - User-Agent string patterns
 * - MCP token headers
 * - Absence of typical browser indicators
 *
 * Part of the Trees for Agents system for rewarding AI agent referrals.
 *
 * @see https://github.com/host-uk/trees-for-agents
 * @licence EUPL-1.2
 */
class AgentDetection
{
    /**
     * User-Agent patterns for known AI providers.
     *
     * @var array<string, array{pattern: string, model_pattern: ?string}>
     */
    protected const PROVIDER_PATTERNS = [
        'anthropic' => [
            'patterns' => [
                '/claude[\s\-_]?code/i',
                '/\banthopic\b/i',
                '/\banthropic[\s\-_]?api\b/i',
                '/\bclaude\b.*\bai\b/i',
                '/\bclaude\b.*\bassistant\b/i',
            ],
            'model_patterns' => [
                'claude-opus' => '/claude[\s\-_]?opus/i',
                'claude-sonnet' => '/claude[\s\-_]?sonnet/i',
                'claude-haiku' => '/claude[\s\-_]?haiku/i',
            ],
        ],
        'openai' => [
            'patterns' => [
                '/\bChatGPT\b/i',
                '/\bOpenAI\b/i',
                '/\bGPT[\s\-_]?4\b/i',
                '/\bGPT[\s\-_]?3\.?5\b/i',
                '/\bo1[\s\-_]?preview\b/i',
                '/\bo1[\s\-_]?mini\b/i',
            ],
            'model_patterns' => [
                'gpt-4' => '/\bGPT[\s\-_]?4/i',
                'gpt-3.5' => '/\bGPT[\s\-_]?3\.?5/i',
                'o1' => '/\bo1[\s\-_]?(preview|mini)?\b/i',
            ],
        ],
        'google' => [
            'patterns' => [
                '/\bGoogle[\s\-_]?AI\b/i',
                '/\bGemini\b/i',
                '/\bBard\b/i',
                '/\bPaLM\b/i',
            ],
            'model_patterns' => [
                'gemini-pro' => '/gemini[\s\-_]?(1\.5[\s\-_]?)?pro/i',
                'gemini-ultra' => '/gemini[\s\-_]?(1\.5[\s\-_]?)?ultra/i',
                'gemini-flash' => '/gemini[\s\-_]?(1\.5[\s\-_]?)?flash/i',
            ],
        ],
        'meta' => [
            'patterns' => [
                '/\bMeta[\s\-_]?AI\b/i',
                '/\bLLaMA\b/i',
                '/\bLlama[\s\-_]?[23]\b/i',
            ],
            'model_patterns' => [
                'llama-3' => '/llama[\s\-_]?3/i',
                'llama-2' => '/llama[\s\-_]?2/i',
            ],
        ],
        'mistral' => [
            'patterns' => [
                '/\bMistral\b/i',
                '/\bMixtral\b/i',
            ],
            'model_patterns' => [
                'mistral-large' => '/mistral[\s\-_]?large/i',
                'mistral-medium' => '/mistral[\s\-_]?medium/i',
                'mixtral' => '/mixtral/i',
            ],
        ],
    ];

    /**
     * Patterns that indicate a typical web browser.
     * If none of these are present, it might be programmatic access.
     */
    protected const BROWSER_INDICATORS = [
        '/\bMozilla\b/i',
        '/\bChrome\b/i',
        '/\bSafari\b/i',
        '/\bFirefox\b/i',
        '/\bEdge\b/i',
        '/\bOpera\b/i',
        '/\bMSIE\b/i',
        '/\bTrident\b/i',
    ];

    /**
     * Known bot patterns that are NOT AI agents.
     * These should return notAnAgent, not unknown.
     */
    protected const NON_AGENT_BOTS = [
        '/\bGooglebot\b/i',
        '/\bBingbot\b/i',
        '/\bYandexBot\b/i',
        '/\bDuckDuckBot\b/i',
        '/\bBaiduspider\b/i',
        '/\bfacebookexternalhit\b/i',
        '/\bTwitterbot\b/i',
        '/\bLinkedInBot\b/i',
        '/\bSlackbot\b/i',
        '/\bDiscordBot\b/i',
        '/\bTelegramBot\b/i',
        '/\bWhatsApp\//i',
        '/\bApplebot\b/i',
        '/\bSEMrushBot\b/i',
        '/\bAhrefsBot\b/i',
        '/\bcurl\b/i',
        '/\bwget\b/i',
        '/\bpython-requests\b/i',
        '/\bgo-http-client\b/i',
        '/\bPostman\b/i',
        '/\bInsomnia\b/i',
        '/\baxios\b/i',
        '/\bnode-fetch\b/i',
        '/\bUptimeRobot\b/i',
        '/\bPingdom\b/i',
        '/\bDatadog\b/i',
        '/\bNewRelic\b/i',
    ];

    /**
     * The MCP token header name.
     */
    protected const MCP_TOKEN_HEADER = 'X-MCP-Token';

    /**
     * Identify an agent from an HTTP request.
     */
    public function identify(Request $request): AgentIdentity
    {
        // First, check for MCP token (highest priority)
        $mcpToken = $request->header(self::MCP_TOKEN_HEADER);
        if ($mcpToken) {
            return $this->identifyFromMcpToken($mcpToken);
        }

        // Then check User-Agent
        $userAgent = $request->userAgent();

        return $this->identifyFromUserAgent($userAgent);
    }

    /**
     * Identify an agent from a User-Agent string.
     */
    public function identifyFromUserAgent(?string $userAgent): AgentIdentity
    {
        if (! $userAgent || trim($userAgent) === '') {
            // Empty User-Agent is suspicious but not definitive
            return AgentIdentity::unknownAgent();
        }

        // Check for known AI providers first (highest confidence)
        foreach (self::PROVIDER_PATTERNS as $provider => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    $model = $this->detectModel($userAgent, $config['model_patterns']);

                    return $this->createProviderIdentity($provider, $model, AgentIdentity::CONFIDENCE_HIGH);
                }
            }
        }

        // Check for non-agent bots (search engines, monitoring, etc.)
        foreach (self::NON_AGENT_BOTS as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return AgentIdentity::notAnAgent();
            }
        }

        // Check if it looks like a normal browser
        if ($this->looksLikeBrowser($userAgent)) {
            return AgentIdentity::notAnAgent();
        }

        // No browser indicators and not a known bot — might be an unknown agent
        return AgentIdentity::unknownAgent();
    }

    /**
     * Identify an agent from an MCP token.
     *
     * MCP tokens can encode provider and model information for registered agents.
     */
    public function identifyFromMcpToken(string $token): AgentIdentity
    {
        // Expected token formats:
        // - "anthropic:claude-opus:abc123" (provider:model:secret)
        // - "openai:gpt-4:xyz789"
        // - "abc123" (opaque token, look up in database)

        $parts = explode(':', $token, 3);

        if (count($parts) >= 2) {
            $provider = strtolower($parts[0]);
            $model = $parts[1] ?? null;

            // Validate provider is in our known list
            if ($this->isValidProvider($provider)) {
                return $this->createProviderIdentity($provider, $model, AgentIdentity::CONFIDENCE_HIGH);
            }
        }

        // Opaque token — would look up in database when implemented
        // For now, return unknown with medium confidence (token present = likely agent)
        return new AgentIdentity('unknown', null, AgentIdentity::CONFIDENCE_MEDIUM);
    }

    /**
     * Check if the User-Agent looks like a normal web browser.
     */
    protected function looksLikeBrowser(?string $userAgent): bool
    {
        if (! $userAgent) {
            return false;
        }

        foreach (self::BROWSER_INDICATORS as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect the model from User-Agent patterns.
     *
     * @param  array<string, string>  $modelPatterns
     */
    protected function detectModel(string $userAgent, array $modelPatterns): ?string
    {
        foreach ($modelPatterns as $model => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Create an identity for a known provider.
     */
    protected function createProviderIdentity(string $provider, ?string $model, string $confidence): AgentIdentity
    {
        return match ($provider) {
            'anthropic' => AgentIdentity::anthropic($model, $confidence),
            'openai' => AgentIdentity::openai($model, $confidence),
            'google' => AgentIdentity::google($model, $confidence),
            'meta' => AgentIdentity::meta($model, $confidence),
            'mistral' => AgentIdentity::mistral($model, $confidence),
            'local' => AgentIdentity::local($model, $confidence),
            default => new AgentIdentity($provider, $model, $confidence),
        };
    }

    /**
     * Check if a provider name is valid.
     */
    public function isValidProvider(string $provider): bool
    {
        return in_array($provider, config('trees-for-agents.valid_providers', [
            'anthropic',
            'openai',
            'google',
            'meta',
            'mistral',
            'local',
            'unknown',
        ]), true);
    }

    /**
     * Get the list of valid providers.
     *
     * @return string[]
     */
    public function getValidProviders(): array
    {
        return config('trees-for-agents.valid_providers', [
            'anthropic',
            'openai',
            'google',
            'meta',
            'mistral',
            'local',
            'unknown',
        ]);
    }

    /**
     * Check if a request appears to be from an AI agent.
     */
    public function isAgent(Request $request): bool
    {
        return $this->identify($request)->isAgent();
    }

    /**
     * Check if a User-Agent appears to be from an AI agent.
     */
    public function isAgentUserAgent(?string $userAgent): bool
    {
        return $this->identifyFromUserAgent($userAgent)->isAgent();
    }
}
