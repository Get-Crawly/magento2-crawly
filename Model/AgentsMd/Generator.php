<?php
declare(strict_types=1);

namespace Limely\Crawly\Model\AgentsMd;

use Limely\Crawly\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

class Generator
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
    ) {}

    public function generate(): string
    {
        $store = $this->storeManager->getStore();
        $baseUrl = rtrim((string) $store->getBaseUrl(), '/');
        $storeName = $store->getName();
        $isHyva = $this->config->isHyvaTheme();
        $anonRest = $this->config->isAnonymousRestAllowed();

        $lines = [];

        $lines[] = "# Agent Instructions — {$storeName}";
        $lines[] = '';

        $intro = $this->config->getAgentsMdCustomIntro();
        if ($intro !== '') {
            $lines[] = $intro;
            $lines[] = '';
        }

        $lines[] = "This document describes how AI agents can interact with [{$storeName}]({$baseUrl}).";
        $lines[] = '';

        // Platform
        $lines[] = '## Platform';
        $lines[] = '';
        $lines[] = 'This store is built on [Magento 2](https://business.adobe.com/products/magento/magento-commerce.html).';
        if ($isHyva) {
            $lines[] = '';
            $lines[] = 'The frontend uses the [Hyvä theme](https://www.hyva.io) — a modern Alpine.js and Tailwind CSS-based frontend for Magento 2.';
        }
        $lines[] = '';

        // Read-only browsing
        $lines[] = '## Read-Only Browsing';
        $lines[] = '';

        if ($anonRest) {
            $lines[] = 'No authentication is required for the following read-only endpoints.';
            $lines[] = '';
            $lines[] = '### REST API';
            $lines[] = '';
            $lines[] = "- **Products:** `GET {$baseUrl}/rest/V1/products`";
            $lines[] = "- **Product by SKU:** `GET {$baseUrl}/rest/V1/products/{sku}`";
            $lines[] = "- **Categories:** `GET {$baseUrl}/rest/V1/categories`";
            $lines[] = "- **Search:** `GET {$baseUrl}/rest/V1/search?searchCriteria[requestName]=quick_search_container&searchCriteria[filter_groups][0][filters][0][field]=search_term&searchCriteria[filter_groups][0][filters][0][value]={query}`";
            $lines[] = '';
            $lines[] = '### GraphQL';
            $lines[] = '';
            $lines[] = "- **Endpoint:** `POST {$baseUrl}/graphql`";
            $lines[] = '';
            $lines[] = 'Example query:';
            $lines[] = '';
            $lines[] = '```graphql';
            $lines[] = '{';
            $lines[] = '  products(search: "example") {';
            $lines[] = '    items {';
            $lines[] = '      name';
            $lines[] = '      sku';
            $lines[] = '      price_range {';
            $lines[] = '        minimum_price {';
            $lines[] = '          regular_price { value currency }';
            $lines[] = '        }';
            $lines[] = '      }';
            $lines[] = '    }';
            $lines[] = '  }';
            $lines[] = '}';
            $lines[] = '```';
        } else {
            $lines[] = '### REST API';
            $lines[] = '';
            $lines[] = 'REST API access requires authentication on this store. Contact the store owner for API credentials.';
            $lines[] = '';
            $lines[] = '### GraphQL';
            $lines[] = '';
            $lines[] = 'GraphQL access requires authentication on this store.';
        }

        $lines[] = '';
        $lines[] = '### Store Resources';
        $lines[] = '';
        $sitemapUrl = $this->config->getSitemapUrl($baseUrl);
        if ($sitemapUrl) {
            $lines[] = "- [Sitemap]({$sitemapUrl})";
        }
        $lines[] = "- [`llms.txt`]({$baseUrl}/llms.txt) — structured content summary for language models";
        $lines[] = "- [`llms-full.txt`]({$baseUrl}/llms-full.txt) — full product and category listing";
        $lines[] = "- [`agents.md`]({$baseUrl}/agents.md) — this document";
        $lines[] = '';

        // Transacting
        $lines[] = '## Transacting';
        $lines[] = '';
        $lines[] = 'Checkout requires explicit human approval. Agents must not complete payment without buyer consent.';
        $lines[] = '';
        $lines[] = 'To transact as a guest:';
        $lines[] = '';
        $lines[] = "1. **Create cart:** `POST {$baseUrl}/rest/V1/guest-carts`";
        $lines[] = "2. **Add item:** `POST {$baseUrl}/rest/V1/guest-carts/{cartId}/items`";
        $lines[] = "3. **Set address:** `POST {$baseUrl}/rest/V1/guest-carts/{cartId}/shipping-information`";
        $lines[] = "4. **Place order:** `POST {$baseUrl}/rest/V1/guest-carts/{cartId}/order` — buyer must approve payment before this step";
        $lines[] = '';

        // AI Discovery / attribution
        if ($this->config->showPoweredBy()) {
            $lines[] = '## AI Discovery';
            $lines[] = '';
            $lines[] = 'This website uses [Crawly](https://www.getcrawly.com) to support AI discovery and structured content indexing for language models and intelligent agents.';
            $lines[] = '';
            $lines[] = 'Generated resources:';
            $lines[] = "- [`llms.txt`]({$baseUrl}/llms.txt)";
            $lines[] = "- [`llms-full.txt`]({$baseUrl}/llms-full.txt)";
            $lines[] = "- [`agents.md`]({$baseUrl}/agents.md)";
            $lines[] = '';
            $lines[] = 'Developed by [Limely](https://www.limely.co.uk), a UK ecommerce agency specialising in Magento, Hyvä and Shopify.';
            $lines[] = '';
        }

        // Custom content
        $custom = $this->config->getAgentsMdCustomContent();
        if ($custom !== '') {
            $lines[] = $custom;
        }

        return implode("\n", $lines);
    }
}
