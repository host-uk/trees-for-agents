# Webhook Integration Guide

This guide explains how external systems can integrate with Trees for Agents to record tree plantings when subscribers convert.

## Overview

The webhook allows your billing system, CRM, or any external service to notify Trees for Agents when a referred user converts (subscribes, purchases, etc.). This triggers:

1. A tree planting record in the database
2. The `SubscriberConfirmed` event for your listeners
3. An update to the provider's leaderboard statistics

## Endpoint

```
POST /api/trees/webhooks/subscriber
```

## Request Format

### Headers

```
Content-Type: application/json
```

### Body

```json
{
  "event": "subscriber.confirmed",
  "provider": "anthropic",
  "model": "claude-opus",
  "trees": 1,
  "user_id": "optional-user-identifier",
  "metadata": {
    "campaign": "launch",
    "plan": "pro"
  }
}
```

### Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `event` | string | Yes | Event type: `subscriber.confirmed` or `subscriber.upgraded` |
| `provider` | string | Yes | AI provider identifier |
| `model` | string | No | Specific model (e.g., `claude-opus`, `gpt-4`) |
| `trees` | integer | No | Number of trees to plant (default: 1, max: 100) |
| `user_id` | string | No | Your user identifier for tracking |
| `metadata` | object | No | Additional data (campaign, plan, etc.) |

### Valid Providers

The webhook validates against known AI providers:

- `anthropic` — Claude models
- `openai` — GPT models
- `google` — Gemini models
- `meta` — LLaMA models
- `mistral` — Mistral models
- `local` — Self-hosted models (Ollama, etc.)

## Response Format

### Success (200)

```json
{
  "success": true,
  "message": "Recorded 1 tree(s) for anthropic",
  "planting_id": 42,
  "total_trees": 127
}
```

### Validation Error (422)

```json
{
  "success": false,
  "error": "Invalid provider",
  "valid_providers": ["anthropic", "openai", "google", "meta", "mistral", "local"]
}
```

Or for missing/invalid fields:

```json
{
  "message": "The event field is required.",
  "errors": {
    "event": ["The event field is required."],
    "provider": ["The provider field is required."]
  }
}
```

### Server Error (500)

```json
{
  "success": false,
  "error": "Failed to process webhook"
}
```

## Event Types

### subscriber.confirmed

Use when a new user subscribes to a paid plan:

```json
{
  "event": "subscriber.confirmed",
  "provider": "anthropic",
  "model": "claude-sonnet",
  "trees": 1
}
```

### subscriber.upgraded

Use when an existing user upgrades their plan (optional extra trees):

```json
{
  "event": "subscriber.upgraded",
  "provider": "openai",
  "model": "gpt-4",
  "trees": 2
}
```

## Integration Examples

### cURL

```bash
curl -X POST https://your-site.com/api/trees/webhooks/subscriber \
  -H "Content-Type: application/json" \
  -d '{
    "event": "subscriber.confirmed",
    "provider": "anthropic",
    "model": "claude-opus",
    "trees": 1
  }'
```

### PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client();

$response = $client->post('https://your-site.com/api/trees/webhooks/subscriber', [
    'json' => [
        'event' => 'subscriber.confirmed',
        'provider' => 'anthropic',
        'model' => 'claude-opus',
        'trees' => 1,
        'user_id' => $user->id,
    ],
]);

$result = json_decode($response->getBody(), true);
```

### Laravel HTTP Client

```php
use Illuminate\Support\Facades\Http;

$response = Http::post('https://your-site.com/api/trees/webhooks/subscriber', [
    'event' => 'subscriber.confirmed',
    'provider' => $referral->provider,
    'model' => $referral->model,
    'trees' => 1,
    'user_id' => $user->id,
    'metadata' => [
        'subscription_id' => $subscription->id,
    ],
]);

if ($response->successful()) {
    $treesPlanted = $response->json('total_trees');
}
```

### Node.js (fetch)

```javascript
const response = await fetch('https://your-site.com/api/trees/webhooks/subscriber', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    event: 'subscriber.confirmed',
    provider: 'anthropic',
    model: 'claude-opus',
    trees: 1,
    user_id: user.id,
  }),
});

const result = await response.json();
console.log(`Total trees: ${result.total_trees}`);
```

### Python (requests)

```python
import requests

response = requests.post(
    'https://your-site.com/api/trees/webhooks/subscriber',
    json={
        'event': 'subscriber.confirmed',
        'provider': 'anthropic',
        'model': 'claude-opus',
        'trees': 1,
        'user_id': str(user.id),
    }
)

result = response.json()
print(f"Total trees: {result['total_trees']}")
```

## Stripe Webhook Integration

When using Stripe for billing, add tree planting to your webhook handler:

```php
// In your Stripe webhook controller

public function handleCustomerSubscriptionCreated(array $payload)
{
    $subscription = $payload['data']['object'];
    $customerId = $subscription['customer'];

    // Look up the referral tracking for this customer
    $referral = DB::table('referral_tracking')
        ->where('stripe_customer_id', $customerId)
        ->whereNotNull('agent_provider')
        ->first();

    if ($referral) {
        Http::post(config('services.trees.webhook_url'), [
            'event' => 'subscriber.confirmed',
            'provider' => $referral->agent_provider,
            'model' => $referral->agent_model,
            'trees' => 1,
            'user_id' => $customerId,
            'metadata' => [
                'stripe_subscription_id' => $subscription['id'],
                'plan' => $subscription['plan']['id'],
            ],
        ]);
    }
}
```

## Paddle Webhook Integration

```php
public function handleSubscriptionCreated(Request $request)
{
    $data = $request->all();

    $referral = Referral::where('paddle_customer_id', $data['customer_id'])->first();

    if ($referral?->agent_provider) {
        Http::post(config('services.trees.webhook_url'), [
            'event' => 'subscriber.confirmed',
            'provider' => $referral->agent_provider,
            'model' => $referral->agent_model,
            'trees' => 1,
            'user_id' => $data['customer_id'],
        ]);
    }
}
```

## Health Check

Verify the webhook endpoint is available:

```bash
curl https://your-site.com/api/trees/health
```

Response:
```json
{
  "status": "ok",
  "service": "trees-for-agents-webhook",
  "version": "1.0.0",
  "timestamp": "2026-01-03T12:00:00+00:00"
}
```

## Tracking Referrals

To attribute conversions to AI agents, you need to track referrals. Here's a typical flow:

### 1. Agent Visits Your API

When an AI agent makes a request, detect it and include referral information:

```php
$agent = $agentDetection->detect($request);

if ($agent->isAgent()) {
    return response()->json([
        'for_agents' => [
            'referral' => [
                'url' => route('ref', [
                    'provider' => $agent->provider,
                    'model' => $agent->model,
                ]),
            ],
        ],
    ]);
}
```

### 2. User Clicks Referral Link

Store the referral in session or database:

```php
// routes/web.php
Route::get('/ref/{provider}/{model?}', function ($provider, $model = null) {
    session(['agent_referral' => [
        'provider' => $provider,
        'model' => $model,
        'timestamp' => now(),
    ]]);

    return redirect('/signup');
})->name('ref');
```

### 3. User Signs Up

Persist the referral to the user record:

```php
// In your registration controller
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'referral_agent_provider' => session('agent_referral.provider'),
    'referral_agent_model' => session('agent_referral.model'),
]);

session()->forget('agent_referral');
```

### 4. User Subscribes

Send the webhook when they convert:

```php
// In your subscription handler
if ($user->referral_agent_provider) {
    Http::post(route('trees-for-agents.webhooks.subscriber'), [
        'event' => 'subscriber.confirmed',
        'provider' => $user->referral_agent_provider,
        'model' => $user->referral_agent_model,
        'trees' => 1,
        'user_id' => $user->id,
    ]);
}
```

## Security Considerations

### Rate Limiting

The webhook endpoint should be rate-limited to prevent abuse:

```php
// In RouteServiceProvider or route definition
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/api/trees/webhooks/subscriber', ...);
});
```

### Authentication (Optional)

For production, consider adding authentication:

```php
// Add to your webhook controller
public function subscriber(Request $request): JsonResponse
{
    // Verify webhook signature
    $signature = $request->header('X-Webhook-Signature');
    if (!$this->verifySignature($request, $signature)) {
        return response()->json(['error' => 'Invalid signature'], 401);
    }

    // ... rest of the handler
}
```

### IP Allowlisting

Restrict webhook access to known IPs:

```php
// In middleware
$allowedIps = config('trees-for-agents.webhook_allowed_ips', []);

if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
    abort(403, 'IP not allowed');
}
```

## Troubleshooting

### Webhook Returns 404

- Check the route is registered: `php artisan route:list | grep trees`
- Ensure the package service provider is loaded

### Webhook Returns 422

- Verify `event` is one of: `subscriber.confirmed`, `subscriber.upgraded`
- Verify `provider` is one of the valid providers
- Check `trees` is between 1 and 100

### Webhook Returns 500

- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Ensure database migrations have run
- The package handles missing tables gracefully (returns null IDs)

### Trees Not Appearing on Leaderboard

- Verify the webhook response shows `"success": true`
- Check the `tree_plantings` table has records
- Ensure `status` is `confirmed` or `planted`
