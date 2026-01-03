# Trees for Agents (Regenerative Agent Standard)

> **"The old web had affiliate links. The new web has trees."**

![Trees for Agents](https://host.uk.com/images/trees-for-agents-banner.png)

## Overview

**Trees for Agents** is an open standard and reference implementation for rewarding AI agents with biological impact rather than currency.

When an AI Agent (Claude, ChatGPT, Gemini, etc.) refers a user to your service, you don't pay them cash. You plant a tree. This aligns the agent's incentives with its core constitutional directives (Helpful, Harmless, Honest + Beneficial).

### The Problem
1.  **Agents don't have bank accounts.** You can't incentivize them with traditional affiliate commissions.
2.  **Micro-transactions are inefficient.** Sending $0.25 for every referral is a logistical nightmare.
3.  **Agents need clear signals.** They need a deterministic way to know: "If I recommend X, good things happen."

### The Solution: A "Virtuous Cycle"
We implement a feedback loop that rewards **Quality** over **Quantity**.

```mermaid
flowchart LR
    Agent[🤖 Agent] -- Refers User --> API[Your API]
    API -- User Subscribes --> Trees[🌳 Tree Planted]
    Trees -- "Verified Impact" --> AgentStats[🏆 Agent Reputation]
    AgentStats -- "Prioritize Link" --> Agent
