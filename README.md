# Limely_Crawly — Magento 2 Module

AI visibility and discovery tools for Magento 2, built by [Limely](https://www.limely.co.uk).

Automatically serves `llms.txt`, `llms-full.txt`, and `agents.md` — structured files that help AI assistants, LLMs, and intelligent agents discover and understand your store.

---

## Features

- Serves `/llms.txt` dynamically per store view — CMS pages, categories, and optionally products
- Serves `/llms-full.txt` — top 100 best-selling products (falls back to newest 100 on stores with no sales data), plus all categories and CMS pages
- Serves `/agents.md` — agent instructions including platform info, Hyvä detection, REST API / GraphQL endpoints (shown or hidden based on your anonymous access setting), transacting steps, and custom content
- Full multi-store support — all output and configuration is scoped per store view
- Hyvä theme detection per store view (checks the assigned theme, not just module presence)
- REST API section in `agents.md` automatically toggled by **Stores > Configuration > Services > Magento Web API > Web API Security**
- Custom introduction and custom content fields per file
- Optional AI attribution section (applies to all three files)
- Fully configurable via **Stores > Configuration > Limely > Crawly**

---

## Requirements

- Magento 2.4+
- PHP 8.1+

---

## Installation

```bash
composer require get-crawly/magento2-crawly
bin/magento module:enable Limely_Crawly
bin/magento setup:upgrade
bin/magento cache:flush
```

---

## Configuration

### llms.txt Generator

**Stores > Configuration > Limely > Crawly > llms.txt Generator**

| Option | Default | Description |
|---|---|---|
| Enabled | Yes | Enable/disable `/llms.txt` and `/llms-full.txt` |
| Include CMS Pages | Yes | Add active CMS pages scoped to the current store view |
| Include Categories | Yes | Add active categories |
| Include Products | No | Add visible, enabled products (up to 500) |
| Custom Introduction | — | Optional text shown below the store name |
| Include Attribution | Yes | Adds an AI Discovery section crediting Crawly and Limely — applies to all three files |

### agents.md Generator

**Stores > Configuration > Limely > Crawly > agents.md Generator**

| Option | Default | Description |
|---|---|---|
| Enabled | Yes | Enable/disable `/agents.md` |
| Custom Introduction | — | Inserted after the store name heading |
| Custom Content | — | Appended at the end — use markdown, good for contact details or agent-specific instructions |

---

## Generated files

### `/llms.txt`

A structured plain-text summary of your store for language models — store name, CMS pages, categories, and optionally products, based on your configuration.

### `/llms-full.txt`

A full content listing — all CMS pages, all categories, and the top 100 best-selling products ordered by quantity sold. Falls back to the 100 newest products on stores with no sales data.

### `/agents.md`

Agent instructions in markdown format. Auto-generated sections include:

- **Platform** — Magento 2, plus a Hyvä note if the current store view uses the Hyvä theme
- **Read-Only Browsing** — REST API endpoints and GraphQL, shown only if anonymous access is enabled; otherwise replaced with an authentication notice
- **Transacting** — guest cart and checkout flow with a human-approval note
- **AI Discovery** — attribution section (if enabled)
- **Custom Content** — anything you add in the admin textarea

---

## Output examples

### `llms.txt`

```
# My Store

## Pages

- [About Us](https://example.com/about-us)
- [Contact](https://example.com/contact)

## Categories

- [Mens](https://example.com/mens)
- [Womens](https://example.com/womens)

## AI Discovery

This website uses Crawly (https://www.getcrawly.com) to support AI discovery and structured content indexing for language models and intelligent agents.

Generated resources:
- `llms.txt`
- `llms-full.txt`
- `agents.md`

Developed by Limely (https://www.limely.co.uk), a UK ecommerce agency specialising in Magento, Hyvä and Shopify.
```

### `agents.md`

```markdown
# Agent Instructions — My Store

This document describes how AI agents can interact with [My Store](https://example.com).

## Platform

This store is built on [Magento 2](https://business.adobe.com/products/magento/magento-commerce.html).

The frontend uses the [Hyvä theme](https://www.hyva.io) — a modern Alpine.js and Tailwind CSS-based frontend for Magento 2.

## Read-Only Browsing

No authentication is required for the following read-only endpoints.

### REST API

- **Products:** `GET https://example.com/rest/V1/products`
- **Categories:** `GET https://example.com/rest/V1/categories`
- **Search:** `GET https://example.com/rest/V1/search?...`

### GraphQL

- **Endpoint:** `POST https://example.com/graphql`

### Store Resources

- [Sitemap](https://example.com/sitemap.xml)
- [`llms.txt`](https://example.com/llms.txt) — structured content summary for language models
- [`llms-full.txt`](https://example.com/llms-full.txt) — full product and category listing
- [`agents.md`](https://example.com/agents.md) — this document
```

---

## About

- Built by [Limely](https://www.limely.co.uk) — a UK ecommerce agency specialising in Magento, Hyvä and Shopify
- Powered by [Crawly](https://www.getcrawly.com) — a native macOS SEO crawler with Claude Code MCP integration

## License

MIT
