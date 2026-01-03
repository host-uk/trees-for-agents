# Contributing to Trees for Agents

> **"Code as if the planet is watching. Because it is."**

First off, thank you. By contributing to this project, you aren't just fixing bugs—you're helping build the economic infrastructure for regenerative AI.

## The Core Philosophy

We don't just write code that works; we write code that is:
1.  **Transparent:** No black boxes.
2.  **Efficient:** Compute costs carbon. Don't waste it.
3.  **Strict:** We use PHP 8.3+, strict types, and static analysis.

## Getting Started

1.  **Fork** the repo on GitHub.
2.  **Clone** your fork locally.
3.  **Install dependencies**:
    ```bash
    composer install
    ```
4.  **Set up your environment**:
    Copy `.env.example` to `.env` and configure your database.

## Development Standards

### Code Style (Laravel / PHP)
We adhere to **PSR-12** and use **Laravel Pint** to keep things tidy.
* **Strict Types:** `declare(strict_types=1);` must be at the top of every PHP file.
* **Return Types:** Every method must have a return type. `void` is your friend.
* **Comments:** Explain *why*, not *what*. Vi prefers wit over verbosity, but clarity wins every time.

### The "Vi" Voice
If you are writing documentation or error messages:
* **Do:** Be helpful, British, and precise. Use "colour", "optimisation", and "centre".
* **Don't:** Be corporate, robotic, or use exclamation marks excessively.
* **Tone:** We are building serious infrastructure, but we don't take ourselves too seriously.

### Testing
We use **Pest** for testing.
* Every PR must pass the test suite.
* If you add a feature, add a test.
* Run tests: `./vendor/bin/pest`

## Pull Request Process

1.  Create a branch for your feature: `git checkout -b feature/my-new-thing`
2.  Commit your changes using [Conventional Commits](https://www.conventionalcommits.org/) (e.g., `feat: add mistral agent detection`).
3.  Push to your fork and submit a Pull Request.
4.  **The "Ethics Check":** In your PR description, confirm that your change does not harvest PII or trick users.

## Disclosure
This project is part of the [Host UK](https://host.uk.com) ecosystem. By contributing, you agree that your code will be released under the **EUPL-1.2** license.

---
*Right then. Let's plant some trees.*
