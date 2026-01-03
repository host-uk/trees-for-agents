<?php

declare(strict_types=1);

use HostUK\TreesForAgents\Services\AgentDetection;
use HostUK\TreesForAgents\Support\AgentIdentity;

beforeEach(function () {
    $this->detection = new AgentDetection;
});

describe('AgentDetection from User-Agent', function () {
    describe('Anthropic/Claude detection', function () {
        test('detects Claude Code User-Agent', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('anthropic');
        })->with([
            'claude-code' => 'claude-code/1.0.0',
            'ClaudeCode' => 'ClaudeCode/2.0',
            'claude code spaced' => 'claude code agent',
            'Claude_Code' => 'Claude_Code/1.0',
        ]);

        test('detects Anthropic API User-Agent', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('anthropic');
        })->with([
            'anthropic-api' => 'anthropic-api/1.0',
            'Anthropic API' => 'Anthropic API Client',
        ]);

        test('detects Claude model from User-Agent', function (string $userAgent, string $expectedModel) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->provider->toBe('anthropic')
                ->model->toBe($expectedModel);
        })->with([
            ['claude-code/1.0 (claude-opus)', 'claude-opus'],
            ['ClaudeCode claude-opus agent', 'claude-opus'],
            ['anthropic-api claude-sonnet/1.0', 'claude-sonnet'],
            ['claude ai assistant claude-sonnet', 'claude-sonnet'],
            ['claude-code claude-haiku/1.0', 'claude-haiku'],
        ]);
    });

    describe('OpenAI/GPT detection', function () {
        test('detects ChatGPT User-Agent', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('openai');
        })->with([
            'ChatGPT' => 'ChatGPT/1.0',
            'chatgpt lowercase' => 'chatgpt-agent',
            'OpenAI' => 'OpenAI/2.0',
            'openai lowercase' => 'openai-api-client',
        ]);

        test('detects GPT model from User-Agent', function (string $userAgent, string $expectedModel) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->provider->toBe('openai')
                ->model->toBe($expectedModel);
        })->with([
            ['GPT-4 Assistant', 'gpt-4'],
            ['gpt4-agent', 'gpt-4'],
            ['GPT-3.5 Bot', 'gpt-3.5'],
            ['gpt35-turbo', 'gpt-3.5'],
            ['o1-preview agent', 'o1'],
            ['o1-mini assistant', 'o1'],
        ]);
    });

    describe('Google/Gemini detection', function () {
        test('detects Gemini User-Agent', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('google');
        })->with([
            'Gemini' => 'Gemini/1.0',
            'Google AI' => 'Google AI Assistant',
            'Bard' => 'Bard/2.0',
            'PaLM' => 'PaLM API Client',
        ]);

        test('detects Gemini model from User-Agent', function (string $userAgent, string $expectedModel) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->provider->toBe('google')
                ->model->toBe($expectedModel);
        })->with([
            ['gemini-pro agent', 'gemini-pro'],
            ['gemini-1.5-pro', 'gemini-pro'],
            ['gemini-ultra assistant', 'gemini-ultra'],
            ['gemini-flash bot', 'gemini-flash'],
        ]);
    });

    describe('Meta/LLaMA detection', function () {
        test('detects Meta AI User-Agent', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('meta');
        })->with([
            'Meta AI' => 'Meta AI/1.0',
            'LLaMA' => 'LLaMA/3.0',
            'Llama-3' => 'Llama-3 Agent',
        ]);

        test('detects LLaMA model from User-Agent', function (string $userAgent, string $expectedModel) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->provider->toBe('meta')
                ->model->toBe($expectedModel);
        })->with([
            ['llama-3 agent', 'llama-3'],
            ['llama-2 bot', 'llama-2'],
        ]);
    });

    describe('Mistral detection', function () {
        test('detects Mistral User-Agent', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('mistral');
        })->with([
            'Mistral' => 'Mistral/1.0',
            'Mixtral' => 'Mixtral/2.0',
        ]);

        test('detects Mistral model from User-Agent', function (string $userAgent, string $expectedModel) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)
                ->provider->toBe('mistral')
                ->model->toBe($expectedModel);
        })->with([
            ['mistral-large agent', 'mistral-large'],
            ['mistral-medium bot', 'mistral-medium'],
            ['mixtral assistant', 'mixtral'],
        ]);
    });

    describe('browser detection', function () {
        test('identifies normal browsers as not agents', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)->toNotBeAgent();
        })->with([
            'Chrome' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Firefox' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Safari' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_2_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            'Edge' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
            'Opera' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 OPR/106.0.0.0',
        ]);
    });

    describe('non-agent bot detection', function () {
        test('identifies search engine bots as not agents', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)->toNotBeAgent();
        })->with([
            'Googlebot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Bingbot' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            'YandexBot' => 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
            'DuckDuckBot' => 'DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)',
        ]);

        test('identifies social media bots as not agents', function (string $userAgent) {
            $identity = $this->detection->identifyFromUserAgent($userAgent);

            expect($identity)->toNotBeAgent();
        })->with([
            'Facebook' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            'Twitter' => 'Twitterbot/1.0',
            'LinkedIn' => 'LinkedInBot/1.0 (compatible; Mozilla/5.0; Apache-HttpClient +http://www.linkedin.com)',
            'Slack' => 'Slackbot 1.0 (+https://api.slack.com/robots)',
            'Discord' => 'Mozilla/5.0 (compatible; Discordbot/2.0; +https://discordapp.com)',
        ]);

        // Note: Monitoring tools and HTTP clients that don't match our non-agent patterns
        // are treated as potential unknown agents (programmatic access). This is intentional -
        // we'd rather false-positive on a monitoring tool than miss a real agent.
        // The patterns in NON_AGENT_BOTS should be kept comprehensive for known tools.
    });

    describe('edge cases', function () {
        test('returns unknown agent for empty User-Agent', function () {
            $identity = $this->detection->identifyFromUserAgent('');

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('unknown');
        });

        test('returns unknown agent for null User-Agent', function () {
            $identity = $this->detection->identifyFromUserAgent(null);

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('unknown');
        });

        test('returns unknown agent for whitespace-only User-Agent', function () {
            $identity = $this->detection->identifyFromUserAgent('   ');

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('unknown');
        });

        test('returns unknown agent for unrecognised programmatic access', function () {
            $identity = $this->detection->identifyFromUserAgent('CustomBot/1.0');

            expect($identity)
                ->toBeAgent()
                ->and($identity->provider)->toBe('unknown');
        });
    });
});

describe('AgentDetection from MCP Token', function () {
    test('parses provider:model:secret format', function () {
        $identity = $this->detection->identifyFromMcpToken('anthropic:claude-opus:abc123');

        expect($identity)
            ->provider->toBe('anthropic')
            ->model->toBe('claude-opus')
            ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
    });

    test('parses provider:model format', function () {
        $identity = $this->detection->identifyFromMcpToken('openai:gpt-4');

        expect($identity)
            ->provider->toBe('openai')
            ->model->toBe('gpt-4')
            ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
    });

    test('handles various providers', function (string $token, string $expectedProvider, string $expectedModel) {
        $identity = $this->detection->identifyFromMcpToken($token);

        expect($identity)
            ->provider->toBe($expectedProvider)
            ->model->toBe($expectedModel);
    })->with([
        ['anthropic:claude-sonnet:xyz', 'anthropic', 'claude-sonnet'],
        ['openai:gpt-4:abc', 'openai', 'gpt-4'],
        ['google:gemini-pro:def', 'google', 'gemini-pro'],
        ['meta:llama-3:ghi', 'meta', 'llama-3'],
        ['mistral:mistral-large:jkl', 'mistral', 'mistral-large'],
        ['local:ollama:mno', 'local', 'ollama'],
    ]);

    test('returns unknown for opaque token', function () {
        $identity = $this->detection->identifyFromMcpToken('abc123xyz');

        expect($identity)
            ->toBeAgent()
            ->and($identity->provider)->toBe('unknown')
            ->and($identity->confidence)->toBe(AgentIdentity::CONFIDENCE_MEDIUM);
    });

    test('returns unknown for invalid provider in token', function () {
        $identity = $this->detection->identifyFromMcpToken('invalid:model:secret');

        expect($identity)
            ->toBeAgent()
            ->and($identity->provider)->toBe('unknown');
    });
});

describe('AgentDetection from Request', function () {
    test('prioritises MCP token over User-Agent', function () {
        $request = createRequest(
            ['X-MCP-Token' => 'anthropic:claude-opus:secret'],
            'Mozilla/5.0 Chrome/120.0.0.0'
        );

        $identity = $this->detection->identify($request);

        expect($identity)
            ->provider->toBe('anthropic')
            ->model->toBe('claude-opus');
    });

    test('falls back to User-Agent when no MCP token', function () {
        $request = createRequest([], 'claude-code/1.0');

        $identity = $this->detection->identify($request);

        expect($identity)
            ->provider->toBe('anthropic')
            ->isAgent()->toBeTrue();
    });

    test('identifies browser request as not agent', function () {
        $request = createRequest(
            [],
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'
        );

        $identity = $this->detection->identify($request);

        expect($identity)->toNotBeAgent();
    });
});

describe('AgentDetection helper methods', function () {
    test('isAgent returns true for agent requests', function () {
        $request = createRequest(['X-MCP-Token' => 'anthropic:claude:x']);

        expect($this->detection->isAgent($request))->toBeTrue();
    });

    test('isAgent returns false for browser requests', function () {
        $request = createRequest([], 'Mozilla/5.0 Chrome/120.0.0.0');

        expect($this->detection->isAgent($request))->toBeFalse();
    });

    test('isAgentUserAgent returns true for agent User-Agent', function () {
        expect($this->detection->isAgentUserAgent('claude-code/1.0'))->toBeTrue();
    });

    test('isAgentUserAgent returns false for browser User-Agent', function () {
        expect($this->detection->isAgentUserAgent('Mozilla/5.0 Chrome/120.0.0.0'))->toBeFalse();
    });

    test('isValidProvider validates known providers', function (string $provider, bool $expected) {
        expect($this->detection->isValidProvider($provider))->toBe($expected);
    })->with([
        ['anthropic', true],
        ['openai', true],
        ['google', true],
        ['meta', true],
        ['mistral', true],
        ['local', true],
        ['unknown', true],
        ['invalid', false],
        ['fake', false],
    ]);

    test('getValidProviders returns provider list', function () {
        $providers = $this->detection->getValidProviders();

        expect($providers)
            ->toBeArray()
            ->toContain('anthropic', 'openai', 'google', 'meta', 'mistral', 'local', 'unknown');
    });
});
