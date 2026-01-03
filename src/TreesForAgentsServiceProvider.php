<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents;

use HostUK\TreesForAgents\Livewire\TreesLeaderboard;
use HostUK\TreesForAgents\Services\AgentDetection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

/**
 * Service provider for Trees for Agents package.
 *
 * @see https://github.com/host-uk/trees-for-agents
 * @licence EUPL-1.2
 */
class TreesForAgentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/trees-for-agents.php',
            'trees-for-agents'
        );

        $this->app->singleton(AgentDetection::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerViews();
        $this->registerRoutes();
        $this->registerLivewireComponents();
        $this->registerPublishables();
    }

    /**
     * Register package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'trees-for-agents');
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    /**
     * Get the route group configuration.
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'api/trees',
            'middleware' => ['api'],
        ];
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        if (class_exists(Livewire::class)) {
            Livewire::component('trees-leaderboard', TreesLeaderboard::class);
        }
    }

    /**
     * Register publishable assets.
     */
    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/trees-for-agents.php' => config_path('trees-for-agents.php'),
            ], 'trees-for-agents-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'trees-for-agents-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/trees-for-agents'),
            ], 'trees-for-agents-views');
        }
    }
}
