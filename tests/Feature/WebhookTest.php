<?php

declare(strict_types=1);

use HostUK\TreesForAgents\Events\SubscriberConfirmed;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    // Run migrations for the models
    $this->artisan('migrate', ['--database' => 'testing']);
});

describe('Webhook endpoints', function () {
    describe('health check', function () {
        test('returns ok status', function () {
            $response = $this->getJson('/api/trees/health');

            $response->assertOk()
                ->assertJson([
                    'status' => 'ok',
                    'service' => 'trees-for-agents-webhook',
                ]);
        });
    });

    describe('subscriber webhook', function () {
        test('accepts valid subscriber confirmation', function () {
            Event::fake([SubscriberConfirmed::class]);

            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.confirmed',
                'provider' => 'anthropic',
                'model' => 'claude-opus',
                'trees' => 1,
            ]);

            $response->assertOk()
                ->assertJson([
                    'success' => true,
                ]);

            Event::assertDispatched(SubscriberConfirmed::class, function ($event) {
                return $event->provider === 'anthropic'
                    && $event->model === 'claude-opus'
                    && $event->trees === 1;
            });
        });

        test('accepts subscriber upgrade event', function () {
            Event::fake([SubscriberConfirmed::class]);

            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.upgraded',
                'provider' => 'openai',
                'model' => 'gpt-4',
                'trees' => 3,
            ]);

            $response->assertOk()
                ->assertJson([
                    'success' => true,
                ]);
        });

        test('defaults to 1 tree if not specified', function () {
            Event::fake([SubscriberConfirmed::class]);

            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.confirmed',
                'provider' => 'google',
            ]);

            $response->assertOk();

            Event::assertDispatched(SubscriberConfirmed::class, function ($event) {
                return $event->trees === 1;
            });
        });

        test('rejects invalid provider', function () {
            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.confirmed',
                'provider' => 'invalid-provider',
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error' => 'Invalid provider',
                ]);
        });

        test('rejects invalid event type', function () {
            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'invalid.event',
                'provider' => 'anthropic',
            ]);

            $response->assertStatus(422);
        });

        test('rejects missing required fields', function () {
            $response = $this->postJson('/api/trees/webhooks/subscriber', []);

            $response->assertStatus(422);
        });

        test('accepts optional user_id', function () {
            Event::fake([SubscriberConfirmed::class]);

            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.confirmed',
                'provider' => 'anthropic',
                'user_id' => 'user-123',
            ]);

            $response->assertOk();
        });

        test('accepts optional metadata', function () {
            Event::fake([SubscriberConfirmed::class]);

            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.confirmed',
                'provider' => 'anthropic',
                'metadata' => [
                    'campaign' => 'launch',
                    'source' => 'mcp',
                ],
            ]);

            $response->assertOk();
        });

        test('limits trees to maximum 100', function () {
            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.confirmed',
                'provider' => 'anthropic',
                'trees' => 101,
            ]);

            $response->assertStatus(422);
        });

        test('requires minimum 1 tree', function () {
            $response = $this->postJson('/api/trees/webhooks/subscriber', [
                'event' => 'subscriber.confirmed',
                'provider' => 'anthropic',
                'trees' => 0,
            ]);

            $response->assertStatus(422);
        });
    });
});
