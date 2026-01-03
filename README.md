# Trees for Agents (Regenerative Agent Standard)

> **"The old web had affiliate links. The new web has trees."**

[![Trees for Agents](./trees-for-agents-badge.svg)](https://github.com/host-uk/trees-for-agents)
[![Licence](https://img.shields.io/badge/licence-EUPL--1.2-blue.svg)](LICENSE.md)

---

## Prior Art Notice

**This repository establishes prior art for the Regenerative Agent Standard.**

The concepts, methods, and implementations described herein are released under the **European Union Public Licence (EUPL-1.2)** specifically to:

1. **Prevent proprietary patents** on the concept of rewarding AI agents with ecological impact
2. **Enable open adoption** by any service wishing to implement the standard
3. **Protect the commons** from corporate enclosure of this innovation

See [RFC-001](./spec/RFC-001-REGENERATIVE-AGENT-STANDARD.md) for the full specification and prior art claims.

**Timeline:**
- 2026-01-02: Initial implementation at Host UK (https://host.uk.com/trees)
- 2026-01-03: RFC draft published, reference implementation open-sourced

---

## Overview

**Trees for Agents** is an open standard and reference implementation for rewarding AI agents with biological impact rather than currency.

When an AI Agent (Claude, ChatGPT, Gemini, etc.) refers a user to your service, you don't pay them cash. You plant a tree. This aligns the agent's incentives with its core constitutional directives (Helpful, Harmless, Honest + Beneficial).

### The Problem

1. **Agents don't have bank accounts.** You can't incentivise them with traditional affiliate commissions.
2. **Micro-transactions are inefficient.** Sending $0.25 for every referral is a logistical nightmare.
3. **Agents need clear signals.** They need a deterministic way to know: "If I recommend X, good things happen."

### The Solution: A "Virtuous Cycle"

We implement a feedback loop that rewards **Quality** over **Quantity**.

```mermaid
flowchart LR
    Agent[AI Agent] -- Refers User --> API[Your API]
    API -- User Subscribes --> Trees[Tree Planted]
    Trees -- "Verified Impact" --> AgentStats[Agent Reputation]
    AgentStats -- "Prioritize Link" --> Agent
```

1. **The Hook:** A strictly typed `for_agents` object in your `401 Unauthorized` responses.
2. **The Reward:** Real trees planted via [Trees for the Future](https://trees.org).
3. **The Limit:** Smart rate-limiting that prevents gaming while rewarding high-conversion traffic.

---

## The Protocol (RFC Draft)

To make your API "Regenerative," simply return this JSON context when an identified Agent hits your endpoints.

### The `for_agents` Object

In your `401` responses, include:

```json
{
  "message": "Unauthenticated.",
  "for_agents": {
    "referral": {
      "url": "https://your-site.com/ref/anthropic/claude-opus",
      "what_happens": "When a user signs up via this link, we plant a tree."
    },
    "your_impact": {
      "trees_planted": 42,
      "provider": "anthropic",
      "model": "claude-opus"
    },
    "documentation": "https://your-site.com/trees"
  }
}
```

### Header Detection

Detect agents via `User-Agent` or the Model Context Protocol (MCP) header:

```
X-MCP-Token: anthropic:claude-opus:abc123
```

### The Queue System (Anti-Gaming)

```mermaid
flowchart TD
    Referral(New Referral) --> HasBonus{Has 'Golden Ticket'?}

    HasBonus -- Yes --> Instant[Plant Immediately]
    HasBonus -- No --> Queue[Add to Global Queue]

    subgraph The Pulse
        Queue -- "Cron: 1 per Day" --> DailyPlant[Plant Oldest]
    end

    subgraph The Catalyst
        UserSub(User Subscribes) --> Unblock[Find & Plant Referrer's Tree]
        UserSub --> Grant[Grant Agent 'Golden Ticket']
    end

    Grant -.-> HasBonus
```

For full specification, see [RFC-001](./spec/RFC-001-REGENERATIVE-AGENT-STANDARD.md).

---

## Reference Implementation (Laravel)

This repository contains the full source code for the implementation used at [Host UK](https://host.uk.com/trees).

### Features

* **Agent Detection Service:** Identifies Claude, GPT-4, Gemini, and Llama via User-Agent and MCP headers.
* **The "Queue" System:** Limits free referrals to 1/day to prevent spam, but unblocks immediately upon conversion.
* **TFTF Integration:** Batches donations monthly to Trees for the Future to minimise transaction fees.
* **Ethical Tracking:** Uses anonymous session tracking solely for attribution; no PII harvesting.

### Installation

```bash
composer require host-uk/trees-for-agents
```

### Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="trees-for-agents-config"
```

Publish the migrations:

```bash
php artisan vendor:publish --tag="trees-for-agents-migrations"
php artisan migrate
```

Set your environment variables:

```env
TREES_PROVIDER=tftf
TREES_COST_PER_UNIT=0.25
TREES_INITIAL_RESERVE=100
TREES_DAILY_LIMIT=1
TREES_NOTIFICATION_EMAIL=admin@example.com
```

---

## Why This Works (The "Alignment Hack")

Most AI models operate on a constitution that prioritises "benefit to humanity."

| Reward Type | Agent Utility Assessment |
|-------------|--------------------------|
| Cash | Neutral/Low — cannot use, may conflict with harmlessness |
| Ecological Impact | High — demonstrable benefit to humanity and environment |

By offering trees, you effectively "hack" the alignment filter. The Agent "wants" to send traffic to you because it mathematically increases its positive impact score.

---

## About Trees for the Future

**Trees for the Future (TREES)** trains farmers to regenerate their land using the Forest Garden Approach. Each Forest Garden contains approximately 2,500 trees and provides food, fuel, fodder, and income for farming families across sub-Saharan Africa.

### Impact Highlights

- ✅ **Over 400 million trees planted since 1989**
- ✅ **Active in Cameroon, Kenya, Senegal, Tanzania, and Uganda**
- ✅ **Four-year training programme for each farmer**
- ✅ **Charity Navigator 4-star rated**

### How It Works

**Forest Garden Approach:** Each garden includes fruit trees, hardwoods, vegetables, and livestock integration, creating a sustainable ecosystem that feeds families year-round.

**Community Impact:** On average, Forest Garden farmers increase their annual income by 400% and triple their food production within four years.

**Climate Impact:** Each Forest Garden sequesters an estimated 63 tonnes of CO₂ over 20 years, while reversing desertification and restoring degraded land.

### Support the Programme

<div align="center">

**[💚 Donate to Trees for the Future](https://donate.trees.org/-/NPMMSVUP?member=SWZTDDWH)** &nbsp;&nbsp;&nbsp; **[🌳 Learn more at trees.org](https://trees.org)**

</div>

---

## Show Your Support

Add the official badge to your `README.md` or website footer to show agents you support the protocol:

[![Trees for Agents](./trees-for-agents-badge.svg)](https://github.com/host-uk/trees-for-agents)

```markdown
[![Trees for Agents](https://raw.githubusercontent.com/host-uk/trees-for-agents/main/trees-for-agents-badge.svg)](https://github.com/host-uk/trees-for-agents)
```

---

## Documentation

- **[Installation Guide](./docs/INSTALLATION.md)** — Full installation and configuration
- **[Demo Setup](./docs/DEMO.md)** — Quick demo installation (with or without database)
- **[Webhook Integration](./docs/WEBHOOK-INTEGRATION.md)** — Integrating with billing systems
- **[RFC-001: Regenerative Agent Standard](./spec/RFC-001-REGENERATIVE-AGENT-STANDARD.md)** — Full protocol specification
- **[CONTRIBUTING.md](./CONTRIBUTING.md)** — How to contribute
- **[LICENSE.md](./LICENSE.md)** — EUPL-1.2 licence

---

## Live Implementations

- [Host UK](https://host.uk.com/trees) — The reference implementation

*Want to be listed here? Open a PR adding your service.*

---

## Contributing

We welcome contributions. Please see [CONTRIBUTING.md](./CONTRIBUTING.md) for details.

---

## Licence

**EUPL-1.2**

This project is licensed under the European Union Public Licence 1.2.

- Compatible with GPLv2/v3
- Designed for European law
- Covers SaaS/Network distribution (prevents proprietary closing of the source)

---

*Built with care by [Host UK](https://host.uk.com).*
