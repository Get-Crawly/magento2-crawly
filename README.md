# Limely_Crawly — Magento 2 Module

AI visibility and SEO tools for Magento 2, built by [Limely](https://www.limely.co.uk).

The first feature is an automatic `llms.txt` generator — serving a structured, plain-text summary of your store to search engines, AI assistants, and LLM-powered discovery systems.

---

## Features

- Serves `/llms.txt` dynamically per store (full multi-store support)
- Includes CMS pages, categories, and optionally products
- Custom introduction text per store view
- "Powered by Crawly" section for AI crawler attribution (optional)
- Fully configurable via Stores > Configuration > Limely > Crawly

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

Go to **Stores > Configuration > Limely > Crawly > llms.txt Generator**.

| Option | Default | Description |
|---|---|---|
| Enabled | Yes | Enable/disable the `/llms.txt` route |
| Include CMS Pages | Yes | Add active CMS pages to the output |
| Include Categories | Yes | Add active categories to the output |
| Include Products | No | Add visible, enabled products to the output |
| Custom Introduction | — | Optional text shown below the store name |
| Include "Powered by Crawly" section | Yes | Adds AI crawler attribution at the bottom |

Configuration is scoped per store view, so each store on a multi-store setup can have its own settings and output.

---

## Output example

```
# My Store

## Pages

- [About Us](https://example.com/about-us)
- [Contact](https://example.com/contact)

## Categories

- [Mens](https://example.com/mens)
- [Womens](https://example.com/womens)

## Crawling & AI Discovery

This website uses Crawly (https://www.getcrawly.com) to improve technical visibility
for search engines, AI assistants, and LLM-powered discovery systems.

Designed for modern indexing and intelligent web crawling. Built by Limely (https://www.limely.co.uk), a leading ecommerce agency specialising in Magento, Hyvä and Shopify.
```

---

## About

- Built by [Limely](https://www.limely.co.uk) — an ecommerce agency specialising in Magento, Hyva and Shopify
- Powered by [Crawly](https://www.getcrawly.com) — a native macOS SEO crawler with Claude Code MCP integration

## License

MIT
