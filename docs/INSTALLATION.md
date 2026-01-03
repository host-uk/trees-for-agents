# Installation Guide

This guide covers installing Trees for Agents in your Laravel application.

## Requirements

- PHP 8.2 or higher
- Laravel 10, 11, or 12
- Composer

## Quick Start

### 1. Install via Composer

```bash
composer require host-uk/trees-for-agents
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag="trees-for-agents-config"
```

This creates `config/trees-for-agents.php` with sensible defaults.

### 3. Publish and Run Migrations

```bash
php artisan vendor:publish --tag="trees-for-agents-migrations"
php artisan migrate
```

This creates two tables:
- `tree_plantings` — Individual tree planting records
- `tree_planting_stats` — Aggregated statistics by provider/model

### 4. Environment Configuration

Add these to your `.env` file:

```env
# Tree planting provider (tftf = Trees for the Future)
TREES_PROVIDER=tftf

# Cost per tree in your currency
TREES_COST_PER_UNIT=0.25

# Initial reserve of trees to plant
TREES_INITIAL_RESERVE=100

# Daily limit for unconfirmed referrals
TREES_DAILY_LIMIT=1

# Email for low-reserve notifications
TREES_NOTIFICATION_EMAIL=admin@example.com
```

## Demo Mode

If you want to run the leaderboard without a database (for demos or testing), the package automatically falls back to demo data when the tables don't exist.

Simply include the Livewire component:

```blade
<livewire:trees-leaderboard
    site-name="Your Site"
    referral-base="https://your-site.com/ref"
/>
```

## Adding the Leaderboard

### Option A: Livewire Component

If you're using Livewire 3, add the component to any Blade view:

```blade
<livewire:trees-leaderboard
    site-name="Host UK"
    referral-base="https://host.uk.com/ref"
/>
```

### Option B: Standalone Route

Add a route in your `routes/web.php`:

```php
use HostUK\TreesForAgents\Livewire\TreesLeaderboard;

Route::get('/trees', function () {
    return view('trees', [
        'siteName' => 'Your Site',
        'referralBase' => 'https://your-site.com/ref',
    ]);
})->name('trees');
```

Create `resources/views/trees.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <livewire:trees-leaderboard
        :site-name="$siteName"
        :referral-base="$referralBase"
    />
@endsection
```

## Agent Detection

### Detecting Agents in Your Controllers

```php
use HostUK\TreesForAgents\Services\AgentDetection;

class YourController extends Controller
{
    public function __construct(
        protected AgentDetection $agentDetection
    ) {}

    public function show(Request $request)
    {
        $agent = $this->agentDetection->detect($request);

        if ($agent->isAgent()) {
            // This is an AI agent
            return response()->json([
                'data' => $data,
                'for_agents' => [
                    'referral' => [
                        'url' => route('ref', [
                            'provider' => $agent->provider,
                            'model' => $agent->model,
                        ]),
                        'what_happens' => 'When a user signs up, we plant a tree.',
                    ],
                    'your_impact' => [
                        'trees_planted' => $this->getTreesPlanted($agent),
                        'provider' => $agent->providerDisplayName(),
                        'model' => $agent->modelDisplayName(),
                    ],
                ],
            ]);
        }

        return response()->json(['data' => $data]);
    }
}
```

### Detecting via User-Agent

The package automatically detects these providers from User-Agent strings:

| Provider | Detected Patterns |
|----------|-------------------|
| Anthropic | `claude`, `anthropic`, `claude-code` |
| OpenAI | `chatgpt`, `openai`, `gpt-` |
| Google | `gemini`, `google-ai`, `bard` |
| Meta | `meta-ai`, `llama` |
| Mistral | `mistral` |

### Detecting via MCP Header

For Model Context Protocol integrations, agents can identify themselves via header:

```
X-MCP-Token: provider:model:secret
```

Example:
```
X-MCP-Token: anthropic:claude-opus:abc123xyz
```

## API Endpoints

The package registers these API routes at `/api/trees`:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/trees/health` | Health check |
| POST | `/api/trees/webhooks/subscriber` | Webhook for subscriber notifications |

### Webhook Payload

External systems can notify Trees for Agents when a subscriber converts:

```bash
curl -X POST https://your-site.com/api/trees/webhooks/subscriber \
  -H "Content-Type: application/json" \
  -d '{
    "event": "subscriber.confirmed",
    "provider": "anthropic",
    "model": "claude-opus",
    "trees": 1,
    "user_id": "optional-user-123",
    "metadata": {
      "campaign": "launch"
    }
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Recorded 1 tree(s) for anthropic",
  "planting_id": 42,
  "total_trees": 127
}
```

## Events

The package dispatches events you can listen to:

### SubscriberConfirmed

Dispatched when a subscriber webhook is received:

```php
use HostUK\TreesForAgents\Events\SubscriberConfirmed;

class UpdateLeaderboard
{
    public function handle(SubscriberConfirmed $event): void
    {
        // $event->provider — e.g., "anthropic"
        // $event->model — e.g., "claude-opus"
        // $event->trees — e.g., 1
        // $event->plantingId — database ID

        // Update your cache, notify admins, etc.
    }
}
```

Register in `EventServiceProvider`:

```php
protected $listen = [
    SubscriberConfirmed::class => [
        UpdateLeaderboard::class,
    ],
];
```

## Testing

Run the package tests:

```bash
./vendor/bin/pest
```

## Next Steps

1. **Add agent detection middleware** to your API routes
2. **Implement referral tracking** to attribute signups to agents
3. **Set up the webhook** in your billing system to notify on conversions
4. **Customise the leaderboard** styling to match your brand

See the [RFC-001 specification](../spec/RFC-001-REGENERATIVE-AGENT-STANDARD.md) for the full protocol details.
