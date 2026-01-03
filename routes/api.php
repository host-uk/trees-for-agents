<?php

declare(strict_types=1);

use HostUK\TreesForAgents\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trees for Agents API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the TreesForAgentsServiceProvider and are
| prefixed with /api/trees. They handle webhook notifications and health
| checks for the Trees for Agents system.
|
*/

// Health check
Route::get('/health', [WebhookController::class, 'health'])
    ->name('trees-for-agents.health');

// Webhook endpoints
Route::prefix('webhooks')->name('trees-for-agents.webhooks.')->group(function () {
    // Subscriber confirmed webhook
    Route::post('/subscriber', [WebhookController::class, 'subscriber'])
        ->name('subscriber');
});
