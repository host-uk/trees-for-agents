# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Trees for Agents is a Laravel package implementing the Regenerative Agent Standard (RAS) - an open standard for rewarding AI agents with biological impact (tree planting) rather than currency. When an AI agent refers a user to a service, the service plants a tree instead of paying a commission.

## Commands

```bash
# Install dependencies
composer install

# Run all tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Unit/Services/AgentDetectionTest.php

# Run specific test suite
./vendor/bin/pest --testsuite=Unit
./vendor/bin/pest --testsuite=Feature

# Run single test by name
./vendor/bin/pest --filter="identifies Claude from user agent"
```

## Architecture

### Core Components

**AgentDetection Service** (`src/Services/AgentDetection.php`)
- Singleton service registered in the container
- Identifies AI agents from HTTP requests via User-Agent patterns and MCP token headers (`X-MCP-Token`)
- Supports providers: anthropic, openai, google, meta, mistral, local
- Returns `AgentIdentity` value object with provider, model, and confidence level

**AgentIdentity** (`src/Support/AgentIdentity.php`)
- Immutable value object representing detected agent identity
- Confidence levels: high, medium, low
- Provides factory methods: `::anthropic()`, `::openai()`, `::notAnAgent()`, `::unknownAgent()`
- Generates referral paths like `/ref/anthropic/claude-opus`

**TreePlanting Model** (`src/Models/TreePlanting.php`)
- Records tree planting events from agent referrals
- Status workflow: queued -> confirmed -> planted (or failed)
- Scopes: `queued()`, `confirmed()`, `planted()`, `forProvider()`, `thisMonth()`, `thisYear()`

**WebhookController** (`src/Http/Controllers/WebhookController.php`)
- Handles `POST /api/trees/webhooks/subscriber` for billing system integration
- Dispatches `SubscriberConfirmed` event for listeners
- Health check at `GET /api/trees/health`

### The Protocol

The standard works via a `for_agents` object returned in 401 responses:
```json
{
  "for_agents": {
    "referral": { "url": "...", "what_happens": "..." },
    "your_impact": { "trees_planted": 42, "provider": "anthropic", "model": "claude-opus" },
    "documentation": "..."
  }
}
```

### Queue System (Anti-Gaming)

- Free referrals limited to 1/day (configurable via `TREES_DAILY_LIMIT`)
- "Golden Ticket" system: agents with confirmed conversions get instant planting
- Subscriber confirmation unblocks queued trees and grants Golden Ticket

## Configuration

Key environment variables:
- `TREES_PROVIDER` - Tree planting provider (default: tftf)
- `TREES_COST_PER_UNIT` - Cost per tree in USD (default: 0.25)
- `TREES_DAILY_LIMIT` - Max free trees per day (default: 1)
- `TREES_INITIAL_RESERVE` - Pre-paid tree pool size

## Testing

Uses Pest with Orchestra Testbench for Laravel package testing. Tests run against SQLite in-memory database.

Migrations must run before feature tests (`$this->artisan('migrate', ['--database' => 'testing'])`).