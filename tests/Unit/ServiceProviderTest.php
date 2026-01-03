<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Tests\Unit;

use HostUK\TreesForAgents\Services\AgentDetection;
use HostUK\TreesForAgents\TreesForAgentsServiceProvider;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [TreesForAgentsServiceProvider::class];
    }

    /** @test */
    public function it_registers_the_agent_detection_service(): void
    {
        $this->assertInstanceOf(
            AgentDetection::class,
            $this->app->make(AgentDetection::class)
        );
    }

    /** @test */
    public function it_registers_the_config(): void
    {
        $this->assertNotNull(config('trees-for-agents'));
        $this->assertIsArray(config('trees-for-agents.providers'));
    }

    /** @test */
    public function it_registers_the_health_route(): void
    {
        $routes = collect(Route::getRoutes())->map(fn($route) => [
            'uri' => $route->uri(),
            'name' => $route->getName(),
        ]);

        $healthRoute = $routes->firstWhere('name', 'trees-for-agents.health');

        $this->assertNotNull($healthRoute);
        $this->assertEquals('api/trees/health', $healthRoute['uri']);
    }

    /** @test */
    public function it_registers_the_webhook_route(): void
    {
        $routes = collect(Route::getRoutes())->map(fn($route) => [
            'uri' => $route->uri(),
            'name' => $route->getName(),
        ]);

        $webhookRoute = $routes->firstWhere('name', 'trees-for-agents.webhooks.subscriber');

        $this->assertNotNull($webhookRoute);
        $this->assertEquals('api/trees/webhooks/subscriber', $webhookRoute['uri']);
    }
}
