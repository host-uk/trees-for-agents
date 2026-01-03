# Demo Installation

This guide helps you set up a quick demo of Trees for Agents to showcase the leaderboard and webhook system.

## Quick Demo (No Database)

The package includes demo mode that works without any database. Perfect for showcasing the concept.

### 1. Create a Fresh Laravel App

```bash
composer create-project laravel/laravel trees-demo
cd trees-demo
```

### 2. Install Dependencies

```bash
composer require host-uk/trees-for-agents livewire/livewire
```

### 3. Create the Demo Route

Edit `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/trees', function () {
    return view('trees');
})->name('trees');
```

### 4. Create the Trees View

Create `resources/views/trees.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trees for Agents - Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-slate-50">
    <livewire:trees-leaderboard
        site-name="Demo Site"
        referral-base="https://demo.example.com/ref"
    />

    @livewireScripts
</body>
</html>
```

### 5. Start the Server

```bash
php artisan serve
```

Visit `http://localhost:8000/trees` to see the demo leaderboard with sample data.

## Full Demo (With Database)

For a complete demo with working webhooks and persistent data:

### 1. Set Up Database

Create a SQLite database (simplest for demos):

```bash
touch database/database.sqlite
```

Update `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### 2. Run Migrations

```bash
php artisan vendor:publish --tag="trees-for-agents-migrations"
php artisan migrate
```

### 3. Seed Demo Data (Optional)

Create `database/seeders/TreesDemoSeeder.php`:

```php
<?php

namespace Database\Seeders;

use HostUK\TreesForAgents\Models\TreePlanting;
use Illuminate\Database\Seeder;

class TreesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['provider' => 'anthropic', 'model' => 'claude-opus', 'trees' => 127],
            ['provider' => 'anthropic', 'model' => 'claude-sonnet', 'trees' => 89],
            ['provider' => 'openai', 'model' => 'gpt-4', 'trees' => 64],
            ['provider' => 'openai', 'model' => 'gpt-4o', 'trees' => 42],
            ['provider' => 'google', 'model' => 'gemini-pro', 'trees' => 31],
            ['provider' => 'mistral', 'model' => 'mistral-large', 'trees' => 18],
            ['provider' => 'meta', 'model' => 'llama-3', 'trees' => 12],
        ];

        foreach ($providers as $data) {
            for ($i = 0; $i < $data['trees']; $i++) {
                TreePlanting::create([
                    'provider' => $data['provider'],
                    'model' => $data['model'],
                    'trees' => 1,
                    'source' => 'demo',
                    'status' => TreePlanting::STATUS_CONFIRMED,
                    'confirmed_at' => now()->subDays(rand(0, 90)),
                ]);
            }
        }
    }
}
```

Run the seeder:

```bash
php artisan db:seed --class=TreesDemoSeeder
```

## Testing the Webhook

### Health Check

```bash
curl http://localhost:8000/api/trees/health
```

Expected response:
```json
{
  "status": "ok",
  "service": "trees-for-agents-webhook",
  "version": "1.0.0",
  "timestamp": "2026-01-03T12:00:00+00:00"
}
```

### Send a Test Webhook

```bash
curl -X POST http://localhost:8000/api/trees/webhooks/subscriber \
  -H "Content-Type: application/json" \
  -d '{
    "event": "subscriber.confirmed",
    "provider": "anthropic",
    "model": "claude-opus",
    "trees": 1
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Recorded 1 tree(s) for anthropic",
  "planting_id": 384,
  "total_trees": 128
}
```

### Test Invalid Provider

```bash
curl -X POST http://localhost:8000/api/trees/webhooks/subscriber \
  -H "Content-Type: application/json" \
  -d '{
    "event": "subscriber.confirmed",
    "provider": "fake-provider"
  }'
```

Expected response (422):
```json
{
  "success": false,
  "error": "Invalid provider",
  "valid_providers": ["anthropic", "openai", "google", "meta", "mistral", "local"]
}
```

## Integrating with Your Billing System

When a user subscribes, your billing system should send a webhook:

### Stripe Example

In your Stripe webhook handler:

```php
use Illuminate\Support\Facades\Http;

public function handleSubscriptionCreated($payload)
{
    $subscription = $payload['data']['object'];

    // Get the referring agent from your session/database
    $referral = ReferralTracking::where('user_id', $subscription['customer'])
        ->whereNotNull('agent_provider')
        ->first();

    if ($referral) {
        Http::post(config('app.url') . '/api/trees/webhooks/subscriber', [
            'event' => 'subscriber.confirmed',
            'provider' => $referral->agent_provider,
            'model' => $referral->agent_model,
            'trees' => 1,
            'user_id' => $subscription['customer'],
            'metadata' => [
                'subscription_id' => $subscription['id'],
                'plan' => $subscription['plan']['id'],
            ],
        ]);
    }
}
```

### Laravel Cashier Example

Listen for the subscription event:

```php
use Laravel\Cashier\Events\SubscriptionCreated;
use Illuminate\Support\Facades\Http;

class PlantTreeOnSubscription
{
    public function handle(SubscriptionCreated $event): void
    {
        $user = $event->subscription->owner;

        if ($user->referral_agent_provider) {
            Http::post(route('trees-for-agents.webhooks.subscriber'), [
                'event' => 'subscriber.confirmed',
                'provider' => $user->referral_agent_provider,
                'model' => $user->referral_agent_model,
                'trees' => 1,
                'user_id' => $user->id,
            ]);
        }
    }
}
```

## Customising the Leaderboard

### Using Your Own View

Publish the views:

```bash
php artisan vendor:publish --tag="trees-for-agents-views"
```

This copies the Blade templates to `resources/views/vendor/trees-for-agents/`. Edit them to match your brand.

### Styling with Your CSS

The default template uses Tailwind CSS classes. You can:

1. Replace the Tailwind CDN with your own build
2. Override specific classes in your published views
3. Use CSS variables for colours (coming soon)

## Deployment Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure your production database
- [ ] Set `TREES_NOTIFICATION_EMAIL` for low-reserve alerts
- [ ] Add the webhook URL to your billing system
- [ ] Test the webhook with a real subscription
- [ ] Add the Trees for Agents badge to your README

## Live Demo

See the reference implementation at [https://host.uk.com/trees](https://host.uk.com/trees).
