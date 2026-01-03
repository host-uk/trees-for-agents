<?php

declare(strict_types=1);

use HostUK\TreesForAgents\Support\AgentIdentity;

describe('AgentIdentity', function () {
    describe('factory methods', function () {
        test('creates Anthropic identity', function () {
            $identity = AgentIdentity::anthropic('claude-opus');

            expect($identity)
                ->provider->toBe('anthropic')
                ->model->toBe('claude-opus')
                ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
        });

        test('creates OpenAI identity', function () {
            $identity = AgentIdentity::openai('gpt-4');

            expect($identity)
                ->provider->toBe('openai')
                ->model->toBe('gpt-4')
                ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
        });

        test('creates Google identity', function () {
            $identity = AgentIdentity::google('gemini-pro');

            expect($identity)
                ->provider->toBe('google')
                ->model->toBe('gemini-pro')
                ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
        });

        test('creates Meta identity', function () {
            $identity = AgentIdentity::meta('llama-3');

            expect($identity)
                ->provider->toBe('meta')
                ->model->toBe('llama-3')
                ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
        });

        test('creates Mistral identity', function () {
            $identity = AgentIdentity::mistral('mistral-large');

            expect($identity)
                ->provider->toBe('mistral')
                ->model->toBe('mistral-large')
                ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
        });

        test('creates local model identity with medium confidence by default', function () {
            $identity = AgentIdentity::local('ollama');

            expect($identity)
                ->provider->toBe('local')
                ->model->toBe('ollama')
                ->confidence->toBe(AgentIdentity::CONFIDENCE_MEDIUM);
        });

        test('creates not-an-agent identity', function () {
            $identity = AgentIdentity::notAnAgent();

            expect($identity)
                ->provider->toBe('not_agent')
                ->model->toBeNull()
                ->confidence->toBe(AgentIdentity::CONFIDENCE_HIGH);
        });

        test('creates unknown agent identity', function () {
            $identity = AgentIdentity::unknownAgent();

            expect($identity)
                ->provider->toBe('unknown')
                ->model->toBeNull()
                ->confidence->toBe(AgentIdentity::CONFIDENCE_LOW);
        });
    });

    describe('identity checks', function () {
        test('isAgent returns true for AI providers', function (AgentIdentity $identity) {
            expect($identity->isAgent())->toBeTrue();
        })->with([
            'anthropic' => fn () => AgentIdentity::anthropic(),
            'openai' => fn () => AgentIdentity::openai(),
            'google' => fn () => AgentIdentity::google(),
            'meta' => fn () => AgentIdentity::meta(),
            'mistral' => fn () => AgentIdentity::mistral(),
            'local' => fn () => AgentIdentity::local(),
            'unknown' => fn () => AgentIdentity::unknownAgent(),
        ]);

        test('isAgent returns false for not-an-agent', function () {
            $identity = AgentIdentity::notAnAgent();

            expect($identity->isAgent())->toBeFalse();
        });

        test('isNotAgent returns true for regular users', function () {
            $identity = AgentIdentity::notAnAgent();

            expect($identity->isNotAgent())->toBeTrue();
        });

        test('isKnown returns true for known providers', function (AgentIdentity $identity) {
            expect($identity->isKnown())->toBeTrue();
        })->with([
            'anthropic' => fn () => AgentIdentity::anthropic(),
            'openai' => fn () => AgentIdentity::openai(),
            'google' => fn () => AgentIdentity::google(),
        ]);

        test('isKnown returns false for unknown agents', function () {
            expect(AgentIdentity::unknownAgent()->isKnown())->toBeFalse();
        });

        test('isUnknown returns true for unknown provider', function () {
            expect(AgentIdentity::unknownAgent()->isUnknown())->toBeTrue();
        });
    });

    describe('confidence checks', function () {
        test('isHighConfidence returns true for high confidence', function () {
            $identity = AgentIdentity::anthropic('claude-opus', AgentIdentity::CONFIDENCE_HIGH);

            expect($identity->isHighConfidence())->toBeTrue();
        });

        test('isHighConfidence returns false for medium confidence', function () {
            $identity = AgentIdentity::local('ollama', AgentIdentity::CONFIDENCE_MEDIUM);

            expect($identity->isHighConfidence())->toBeFalse();
        });

        test('isMediumConfidenceOrHigher returns true for high', function () {
            $identity = AgentIdentity::anthropic();

            expect($identity->isMediumConfidenceOrHigher())->toBeTrue();
        });

        test('isMediumConfidenceOrHigher returns true for medium', function () {
            $identity = AgentIdentity::local();

            expect($identity->isMediumConfidenceOrHigher())->toBeTrue();
        });

        test('isMediumConfidenceOrHigher returns false for low', function () {
            $identity = AgentIdentity::unknownAgent();

            expect($identity->isMediumConfidenceOrHigher())->toBeFalse();
        });
    });

    describe('referral paths', function () {
        test('generates referral path with provider and model', function () {
            $identity = AgentIdentity::anthropic('claude-opus');

            expect($identity->getReferralPath())->toBe('/ref/anthropic/claude-opus');
        });

        test('generates referral path with provider only', function () {
            $identity = AgentIdentity::openai();

            expect($identity->getReferralPath())->toBe('/ref/openai');
        });

        test('returns null for not-an-agent', function () {
            $identity = AgentIdentity::notAnAgent();

            expect($identity->getReferralPath())->toBeNull();
        });

        test('generates referral path for unknown agents', function () {
            $identity = AgentIdentity::unknownAgent();

            expect($identity->getReferralPath())->toBe('/ref/unknown');
        });
    });

    describe('display names', function () {
        test('returns correct provider display names', function (string $provider, string $expected) {
            $identity = new AgentIdentity($provider, null, AgentIdentity::CONFIDENCE_HIGH);

            expect($identity->getProviderDisplayName())->toBe($expected);
        })->with([
            ['anthropic', 'Anthropic'],
            ['openai', 'OpenAI'],
            ['google', 'Google'],
            ['meta', 'Meta'],
            ['mistral', 'Mistral'],
            ['local', 'Local Model'],
            ['unknown', 'Unknown Agent'],
            ['not_agent', 'User'],
            ['custom', 'Custom'],
        ]);

        test('returns null model display name when no model', function () {
            $identity = AgentIdentity::anthropic();

            expect($identity->getModelDisplayName())->toBeNull();
        });

        test('normalises Claude model names', function (string $model, string $expected) {
            $identity = AgentIdentity::anthropic($model);

            expect($identity->getModelDisplayName())->toBe($expected);
        })->with([
            ['claude-opus', 'Claude Opus'],
            ['claude-opus-4', 'Claude Opus'],
            ['claude-sonnet', 'Claude Sonnet'],
            ['claude-sonnet-4', 'Claude Sonnet'],
            ['claude-haiku', 'Claude Haiku'],
            ['claude-haiku-3', 'Claude Haiku'],
        ]);

        test('normalises GPT model names', function (string $model, string $expected) {
            $identity = AgentIdentity::openai($model);

            expect($identity->getModelDisplayName())->toBe($expected);
        })->with([
            ['gpt-4', 'GPT-4'],
            ['gpt-4o', 'GPT-4'],
            ['gpt-4-turbo', 'GPT-4'],
            ['gpt-3.5', 'GPT-3.5'],
            ['gpt-3.5-turbo', 'GPT-3.5'],
            ['o1', 'o1'],
            ['o1-preview', 'o1'],
            ['o1-mini', 'o1'],
        ]);

        test('normalises Gemini model names', function (string $model, string $expected) {
            $identity = AgentIdentity::google($model);

            expect($identity->getModelDisplayName())->toBe($expected);
        })->with([
            ['gemini-pro', 'Gemini Pro'],
            ['gemini-1.5-pro', 'Gemini Pro'],
            ['gemini-ultra', 'Gemini Ultra'],
            ['gemini-flash', 'Gemini Flash'],
        ]);
    });

    describe('array conversion', function () {
        test('converts identity to array', function () {
            $identity = AgentIdentity::anthropic('claude-opus');

            expect($identity->toArray())->toBe([
                'provider' => 'anthropic',
                'model' => 'claude-opus',
                'confidence' => 'high',
                'is_agent' => true,
                'referral_path' => '/ref/anthropic/claude-opus',
            ]);
        });

        test('converts not-an-agent to array', function () {
            $identity = AgentIdentity::notAnAgent();

            expect($identity->toArray())->toBe([
                'provider' => 'not_agent',
                'model' => null,
                'confidence' => 'high',
                'is_agent' => false,
                'referral_path' => null,
            ]);
        });
    });
});
