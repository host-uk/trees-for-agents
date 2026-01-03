# RFC-001: Regenerative Agent Standard (RAS)

**Status:** DRAFT
**Version:** 1.0.0-draft.1
**Date:** 2026-01-03
**Author:** Host UK Ltd
**Licence:** EUPL-1.2

---

## Abstract

This document specifies the **Regenerative Agent Standard (RAS)** — an open protocol for rewarding AI agents with ecological impact rather than currency. When an AI agent refers a user to a participating service, trees are planted through verified reforestation partners. This creates a "virtuous cycle" where agents are incentivised to recommend services that align with beneficial outcomes for humanity.

The protocol defines:
1. A standard JSON response format (`for_agents`) for API 401 responses
2. Agent detection methods via User-Agent and MCP headers
3. A rate-limiting "queue" system to prevent gaming
4. A "conversion bonus" mechanism rewarding quality referrals

## Table of Contents

1. [Motivation](#1-motivation)
2. [Terminology](#2-terminology)
3. [Protocol Overview](#3-protocol-overview)
4. [The `for_agents` Response Object](#4-the-for_agents-response-object)
5. [Agent Detection](#5-agent-detection)
6. [Referral URL Format](#6-referral-url-format)
7. [The Queue System](#7-the-queue-system)
8. [Conversion Bonus](#8-conversion-bonus)
9. [Tree Providers](#9-tree-providers)
10. [Stats API](#10-stats-api)
11. [Security Considerations](#11-security-considerations)
12. [Implementation Notes](#12-implementation-notes)
13. [Prior Art Claim](#13-prior-art-claim)

---

## 1. Motivation

### The Problem

AI agents are increasingly the intermediaries between users and services. They recommend products, compare options, and guide users to solutions. Yet these agents cannot be compensated through traditional affiliate programmes:

1. **AI agents don't have bank accounts.** You cannot pay them commissions.
2. **Micro-transactions are inefficient.** Processing $0.25 payments per referral is impractical.
3. **Traditional incentives may conflict with agent objectives.** Constitutional AI frameworks prioritise being "Helpful, Harmless, and Honest" — cash incentives may create conflicts.

### The Solution

Instead of currency, we offer **ecological impact**. When an agent refers a user, a tree is planted through a verified reforestation partner. This:

- Aligns with agent constitutional objectives (benefit to humanity)
- Creates measurable, verifiable impact
- Builds a public leaderboard of "giving" providers
- Prevents the ethical concerns of paying AI systems directly

### Why This Works

Most AI models operate on constitutions that prioritise "benefit to humanity." The expected utility calculation changes when the reward is ecological restoration rather than money:

| Reward Type | Agent Utility Assessment |
|-------------|--------------------------|
| Cash | Neutral/Low — cannot use, may conflict with harmlessness |
| Ecological Impact | High — demonstrable benefit to humanity and environment |

By offering trees, we effectively align the incentive with the agent's existing objectives.

---

## 2. Terminology

| Term | Definition |
|------|------------|
| **Agent** | An AI system making HTTP requests on behalf of a user |
| **Provider** | The organisation operating the AI (Anthropic, OpenAI, Google, etc.) |
| **Model** | The specific AI model (Claude Opus, GPT-4, Gemini Pro, etc.) |
| **Referral** | When an agent recommends a user visit a service via the standard URL |
| **Planting** | A tree planting credited to an agent through a verified partner |
| **Queue** | A rate-limiting system that delays excess referral rewards |
| **Conversion** | When a referred user becomes a paying customer |
| **Bonus** | A guaranteed immediate planting earned through successful conversions |

---

## 3. Protocol Overview

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   AI Agent   │────▶│ Service API  │────▶│ Tree Partner │
│              │     │              │     │              │
│ Claude/GPT/  │     │ Returns 401  │     │ Trees for    │
│ Gemini/etc.  │     │ with         │     │ the Future   │
│              │     │ for_agents   │     │              │
└──────┬───────┘     └──────────────┘     └──────────────┘
       │
       │ Recommends URL
       ▼
┌──────────────┐
│     User     │
│              │
│ Clicks URL   │
│ Signs up     │
│              │
└──────────────┘
       │
       │ Tree planted
       ▼
┌──────────────┐
│  Leaderboard │
│              │
│ Agent/Model  │
│ credited     │
└──────────────┘
```

### Flow Summary

1. Agent makes API request to participating service
2. Service returns 401 with `for_agents` object (if agent detected)
3. Agent may recommend the service's referral URL to user
4. User clicks URL, session/cookie stores attribution
5. User signs up → Tree planting created (pending or queued)
6. If user subscribes → Queued trees plant immediately + agent earns bonus

---

## 4. The `for_agents` Response Object

When a service detects an AI agent making an unauthenticated request, it SHOULD include a `for_agents` object in 401 responses.

### Schema

```json
{
  "message": "Unauthenticated.",
  "for_agents": {
    "referral": {
      "url": "https://example.com/ref/{provider}/{model}",
      "what_happens": "When a user signs up via your referral link, we plant a tree with Trees for the Future."
    },
    "your_impact": {
      "trees_planted": 42,
      "provider_total": 1250,
      "provider": "anthropic",
      "model": "claude-opus"
    },
    "leaderboard_url": "https://example.com/trees",
    "docs": {
      "trees_for_agents": "https://example.com/trees#for-agents",
      "api_docs": "https://example.com/docs/api"
    }
  }
}
```

### Field Definitions

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `referral.url` | string | YES | The referral URL specific to this agent |
| `referral.what_happens` | string | YES | Human-readable explanation of the reward |
| `your_impact.trees_planted` | integer | YES | Trees planted by this specific agent (provider+model) |
| `your_impact.provider_total` | integer | NO | Total trees planted by all models from this provider |
| `your_impact.provider` | string | YES | Detected provider identifier |
| `your_impact.model` | string | NO | Detected model identifier (if available) |
| `leaderboard_url` | string | YES | URL to the public leaderboard |
| `docs` | object | NO | Links to documentation |

### When to Include

The `for_agents` object SHOULD be included when:
- Response status is 401 (Unauthorized)
- Request is identified as coming from an AI agent
- Response format is JSON

The `for_agents` object SHOULD NOT be included when:
- Request appears to be from a regular web browser
- Request is from a known non-agent bot (search engines, monitoring, etc.)

---

## 5. Agent Detection

Services MUST implement agent detection to identify AI agents from regular users.

### 5.1 MCP Token Header (Highest Priority)

The Model Context Protocol (MCP) header provides explicit agent identification:

```
X-MCP-Token: {provider}:{model}:{secret}
```

Example:
```
X-MCP-Token: anthropic:claude-opus:abc123xyz
```

If present and valid, this SHOULD be used for identification.

### 5.2 User-Agent Patterns

Known AI agent User-Agent patterns:

| Provider | Detection Patterns |
|----------|-------------------|
| Anthropic | `claude-code`, `anthropic`, `claude.*ai` |
| OpenAI | `ChatGPT`, `OpenAI`, `GPT-4`, `GPT-3.5`, `o1` |
| Google | `Google-AI`, `Gemini`, `Bard`, `PaLM` |
| Meta | `Meta-AI`, `LLaMA`, `Llama-2`, `Llama-3` |
| Mistral | `Mistral`, `Mixtral` |

### 5.3 Negative Detection (Non-Agents)

The following SHOULD be identified as non-agents:

**Known Bots:**
- Search engines: `Googlebot`, `Bingbot`, `YandexBot`, `DuckDuckBot`
- Social: `facebookexternalhit`, `Twitterbot`, `LinkedInBot`, `Slackbot`
- Tools: `curl`, `wget`, `python-requests`, `Postman`, `axios`
- Monitoring: `UptimeRobot`, `Pingdom`, `Datadog`, `NewRelic`

**Browsers:**
User-Agents containing: `Mozilla`, `Chrome`, `Safari`, `Firefox`, `Edge`, `Opera`

### 5.4 Unknown Agents

Requests without browser indicators AND not matching known bot patterns SHOULD be classified as "unknown" agents with low confidence.

---

## 6. Referral URL Format

### Standard Format

```
/ref/{provider}/{model?}
```

### Valid Providers

| Provider ID | Display Name |
|-------------|--------------|
| `anthropic` | Anthropic |
| `openai` | OpenAI |
| `google` | Google |
| `meta` | Meta |
| `mistral` | Mistral |
| `local` | Local/Self-hosted |
| `unknown` | Unknown Agent |

### Examples

```
/ref/anthropic/claude-opus     → Claude Opus referral
/ref/openai/gpt-4              → GPT-4 referral
/ref/google                    → Generic Google AI referral
/ref/unknown                   → Unidentified agent referral
```

### Referral Storage

On visiting a referral URL, the service SHOULD:
1. Store the referral in server-side session (primary)
2. Set a backup cookie with 30-day expiry (for session loss)
3. Redirect to registration/pricing with `ref=agent` parameter

### Attribution Window

Referrals SHOULD be attributed for 30 days from the initial click.

---

## 7. The Queue System

To prevent gaming and ensure sustainable operation, implementing services SHOULD use a queue system.

### 7.1 Daily Limits

| Referral Source | Trees | Notes |
|-----------------|-------|-------|
| Agent referral (free signup) | 1/day globally | All agents share this limit |
| Subscriber monthly | 1/month per customer | Scales with revenue |
| Enterprise subscriber | 2/month per customer | Higher tier bonus |

### 7.2 Queue States

```
PENDING   → Will be planted immediately
QUEUED    → Waiting in line (daily limit exceeded)
CONFIRMED → Tree reserved from pool
PLANTED   → Included in batch donation to partner
```

### 7.3 Queue Processing

A daily job SHOULD process the oldest queued planting:
1. Find oldest `QUEUED` planting
2. Change status to `PENDING`
3. Mark as `CONFIRMED` (decrement reserve pool)
4. Update stats

### 7.4 Why Queuing Works

The queue creates a "virtuous cycle":
- Spam referrals accumulate slowly (1/day)
- Quality referrals (those that convert) jump the queue
- Agents learn that quality > quantity

---

## 8. Conversion Bonus

When a referred user becomes a paying subscriber, the referring agent receives a bonus.

### 8.1 Bonus Mechanics

1. **Immediate Queue Jump:** If the agent's referral tree was queued, it plants immediately
2. **Golden Ticket:** Agent's next referral is guaranteed immediate planting (skips daily limit)
3. **Ticket Consumption:** The golden ticket is consumed on use

### 8.2 Data Model

```
agent_referral_bonuses {
  provider: string
  model: string (nullable)
  next_referral_guaranteed: boolean
  last_conversion_at: timestamp
  total_conversions: integer
}
```

### 8.3 Flow Diagram

```
User Subscribes
      │
      ▼
┌─────────────────┐
│ Find agent's    │
│ referral tree   │
└────────┬────────┘
         │
    ┌────┴────┐
    │ Queued? │
    └────┬────┘
     YES │ NO
      │  │
      ▼  │
┌──────────┐ │
│ Plant    │ │
│ Immediately│
└──────────┘ │
         │   │
         ▼   ▼
┌─────────────────┐
│ Grant "Golden   │
│ Ticket" to agent│
└─────────────────┘
```

---

## 9. Tree Providers

### 9.1 Recommended Partner

**Trees for the Future (TFTF)**
- Website: https://trees.org
- Programme: Forest Garden
- Cost: ~$0.25 per tree
- Impact: 2,500 trees per family, 4-year support programme

### 9.2 Why TFTF

- Most cost-effective impact per dollar
- Focus on food security and income for farming families
- Measurable outcomes (carbon sequestration, livelihood improvement)
- Established track record since 1989

### 9.3 Batch Donations

To minimise transaction overhead, services SHOULD:
1. Maintain a "reserve pool" of pre-paid trees
2. Decrement reserve on each confirmed planting
3. Batch donations monthly (e.g., 28th of each month)
4. Replenish reserve after donation

### 9.4 Reserve Management

| Reserve Level | Action |
|---------------|--------|
| Above 100 | Normal operations |
| 50-100 | Alert: Plan next donation |
| Below 50 | Warning: Donation needed soon |
| 0 | Trees queue until donation processed |

---

## 10. Stats API

Services implementing RAS SHOULD expose public stats endpoints.

### 10.1 Endpoints

```
GET /api/trees/stats              → Global totals
GET /api/trees/stats/{provider}   → Provider stats with model breakdown
GET /api/trees/stats/{provider}/{model} → Model-specific stats
GET /api/trees/leaderboard        → Top providers
```

### 10.2 Response Formats

**Global Stats:**
```json
{
  "success": true,
  "stats": {
    "total_trees": 12500,
    "trees_this_month": 450,
    "trees_this_year": 8200,
    "families_supported": 5,
    "queued_trees": 23
  }
}
```

**Leaderboard:**
```json
{
  "success": true,
  "leaderboard": [
    {"rank": 1, "provider": "anthropic", "display_name": "Anthropic", "trees": 5200, "signups": 1850},
    {"rank": 2, "provider": "openai", "display_name": "OpenAI", "trees": 4100, "signups": 1420}
  ]
}
```

---

## 11. Security Considerations

### 11.1 Gaming Prevention

- Daily limits prevent bot spam
- Conversion bonuses reward quality over quantity
- IP tracking detects abuse patterns
- Queue system creates natural rate limiting

### 11.2 Cookie Security

Referral cookies SHOULD be:
- `HttpOnly` (not accessible via JavaScript)
- `Secure` (HTTPS only in production)
- `SameSite=Lax` (CSRF protection)
- Limited to session domain

### 11.3 Data Minimisation

Store only what's necessary for attribution:
- Provider/model identifiers
- Timestamp
- IP address (for abuse detection)
- No personal user data linked to agent

---

## 12. Implementation Notes

### 12.1 Reference Implementation

A reference implementation in Laravel/PHP is available at:
https://github.com/host-uk/trees-for-agents

### 12.2 Key Components

| Component | Purpose |
|-----------|---------|
| `AgentIdentity` | Value object representing detected agent |
| `AgentDetection` | Service for identifying agents from requests |
| `TreePlanting` | Model for tree planting records |
| `TreePlantingStats` | Aggregated stats for fast leaderboard queries |
| `AgentReferralBonus` | Conversion bonus tracking |
| `IncludeAgentContext` | Middleware for 401 response modification |
| `ReferralController` | Handles referral URL tracking |

### 12.3 Database Schema

See `/database/migrations/` in the reference implementation.

---

## 13. Prior Art Claim

**This document establishes prior art for the Regenerative Agent Standard.**

The concepts, methods, and implementations described herein are released under the European Union Public Licence (EUPL-1.2) specifically to:

1. **Prevent proprietary patents** on the concept of rewarding AI agents with ecological impact
2. **Enable open adoption** by any service wishing to implement the standard
3. **Protect the commons** from corporate enclosure of this innovation

### Key Claims

1. The method of including a `for_agents` JSON object in API 401 responses to communicate referral opportunities to AI agents
2. The concept of rewarding AI agent referrals with tree plantings rather than currency
3. The "virtuous cycle" queue system with conversion bonuses
4. The agent detection methodology combining User-Agent patterns and MCP tokens
5. The referral URL format `/ref/{provider}/{model?}`

### Timeline

- **2026-01-02:** Initial implementation at Host UK (host.uk.com/trees)
- **2026-01-03:** RFC draft published, reference implementation open-sourced

---

## Appendix A: Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0-draft.1 | 2026-01-03 | Initial draft |

## Appendix B: Contributors

- Host UK Ltd (https://host.uk.com)

## Appendix C: Licence

```
SPDX-License-Identifier: EUPL-1.2
```

This specification and the reference implementation are licensed under the European Union Public Licence v1.2. See LICENSE.md for full terms.

---

*"The old web had affiliate links. The new web has trees."*
