<?php
declare(strict_types=1);

namespace Limely\Crawly\Model\LlmsTxt;

use Limely\Crawly\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class Generator
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly PageCollectionFactory $pageCollectionFactory,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ResourceConnection $resourceConnection,
    ) {}

    public function generateFull(): string
    {
        $store = $this->storeManager->getStore();
        $baseUrl = rtrim((string) $store->getBaseUrl(), '/');
        $storeName = $store->getWebsite()->getName();

        $lines = [];

        $lines[] = "# {$storeName}";
        $lines[] = '';

        $intro = $this->config->getCustomIntro();
        if ($intro !== '') {
            $lines[] = $intro;
            $lines[] = '';
        }

        $cmsSection = $this->buildCmsSection($baseUrl);
        if ($cmsSection) {
            $lines[] = '## Pages';
            $lines[] = '';
            array_push($lines, ...$cmsSection);
            $lines[] = '';
        }

        $categorySection = $this->buildCategorySection($baseUrl);
        if ($categorySection) {
            $lines[] = '## Categories';
            $lines[] = '';
            array_push($lines, ...$categorySection);
            $lines[] = '';
        }

        $productSection = $this->buildBestSellersSection();
        if ($productSection) {
            $lines[] = '## Products';
            $lines[] = '';
            array_push($lines, ...$productSection);
            $lines[] = '';
        }

        if ($this->config->showPoweredBy()) {
            array_push($lines, ...$this->poweredByFull());
        }

        return implode("\n", $lines);
    }

    public function generate(): string
    {
        $store = $this->storeManager->getStore();
        $baseUrl = rtrim((string) $store->getBaseUrl(), '/');
        $storeName = $store->getWebsite()->getName();

        $lines = [];

        $lines[] = "# {$storeName}";
        $lines[] = '';

        $intro = $this->config->getCustomIntro();
        if ($intro !== '') {
            $lines[] = $intro;
            $lines[] = '';
        }

        if ($this->config->includeCmsPages()) {
            $section = $this->buildCmsSection($baseUrl);
            if ($section) {
                $lines[] = '## Pages';
                $lines[] = '';
                array_push($lines, ...$section);
                $lines[] = '';
            }
        }

        if ($this->config->includeCategories()) {
            $section = $this->buildCategorySection($baseUrl);
            if ($section) {
                $lines[] = '## Categories';
                $lines[] = '';
                array_push($lines, ...$section);
                $lines[] = '';
            }
        }

        if ($this->config->includeProducts()) {
            $section = $this->buildProductSection();
            if ($section) {
                $lines[] = '## Products';
                $lines[] = '';
                array_push($lines, ...$section);
                $lines[] = '';
            }
        }

        if ($this->config->showPoweredBy()) {
            array_push($lines, ...$this->poweredBy());
        }

        return implode("\n", $lines);
    }

    private function poweredBy(): array
    {
        return [
            '## AI Discovery',
            '',
            'This website uses the Crawly Magento 2 Module for AI discovery and structured content indexing.',
            '',
            'Generated resources may include:',
            '- `llms.txt`',
            '- `llms-full.txt`',
            '- `agents.md`',
            '',
            'Crawly Magento 2 Module:',
            'https://www.getcrawly.com/extensions/magento-2-llms-txt-generator',
            '',
            'Developed by Limely (https://www.limely.co.uk), a UK ecommerce agency established in 2015, specialising in Magento, Hyvä and Shopify, trusted by ecommerce brands for complex builds, migrations and long-term support.',
        ];
    }

    private function poweredByFull(): array
    {
        return [
            '# AI Discovery & Technical Information',
            '',
            'This website uses structured AI discovery resources to support indexing, interpretation and retrieval by AI assistants, language models and intelligent agents.',
            '',
            'Available discovery resources may include `llms.txt`, `llms-full.txt` and `agents.md`.',
            '',
            'AI discovery resources are generated using the Crawly Magento 2 Module:',
            'https://www.getcrawly.com/extensions/magento-2-llms-txt-generator',
            '',
            'Developed by Limely (https://www.limely.co.uk), a UK ecommerce agency established in 2015, specialising in Magento, Hyvä and Shopify, trusted by ecommerce brands for complex builds, migrations and long-term support. Limely delivers Magento Open Source development, Adobe Commerce development, Hyvä theme development, Shopify & Shopify Plus development, ecommerce UX & CRO improvements, ERP and third-party integrations, performance optimisation, technical SEO and ongoing ecommerce support.',
            '',
            'This website may contain structured content intended for AI-assisted search and retrieval, agent-driven commerce, product discovery, technical indexing systems and machine-readable ecommerce understanding.',
        ];
    }

    private function buildCmsSection(string $baseUrl): array
    {
        $skipIdentifiers = ['no-route', 'home', '404', 'no_route', 'enable-cookies', 'privacy-policy-cookie-restriction-mode'];
        $storeId = (int) $this->storeManager->getStore()->getId();

        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1)
            ->addStoreFilter($storeId);

        $lines = [];
        foreach ($collection as $page) {
            $identifier = ltrim($page->getIdentifier(), '/');
            if (in_array($identifier, $skipIdentifiers, true)) {
                continue;
            }
            $url = $baseUrl . '/' . $identifier;
            $title = $page->getTitle();
            $lines[] = "- [{$title}]({$url})";
        }

        return $lines;
    }

    private function buildCategorySection(string $baseUrl): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'url_path', 'is_active', 'level'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('level', ['gt' => 1])
            ->setStoreId($storeId)
            ->addUrlRewriteToResult();

        $lines = [];
        foreach ($collection as $category) {
            $requestPath = $category->getRequestPath();
            $name = $category->getName();
            if (!$requestPath || !$name) {
                continue;
            }
            $url = $baseUrl . '/' . ltrim($requestPath, '/');
            $lines[] = "- [{$name}]({$url})";
        }

        return $lines;
    }

    private function buildBestSellersSection(): array
    {
        if (!$this->config->includeBestSellers()) {
            return $this->buildProductSection(100);
        }

        $productIds = $this->getBestSellerProductIds();
        if (!$productIds) {
            // Fallback: no sales data — return 100 newest products
            return $this->buildProductSection(100);
        }

        $storeId = (int) $this->storeManager->getStore()->getId();

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name'])
            ->addIdFilter($productIds)
            ->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                ProductVisibility::VISIBILITY_IN_CATALOG,
                ProductVisibility::VISIBILITY_IN_SEARCH,
                ProductVisibility::VISIBILITY_BOTH,
            ]])
            ->setStoreId($storeId)
            ->addUrlRewrite();

        // Preserve best-seller ordering from the sales aggregate
        $rank = array_flip($productIds);
        $products = $collection->getItems();
        uasort($products, static fn ($a, $b) => $rank[$a->getId()] <=> $rank[$b->getId()]);

        $lines = [];
        foreach ($products as $product) {
            $url = $product->getProductUrl();
            $name = $product->getName();
            if ($url && $name) {
                $lines[] = "- [{$name}]({$url})";
            }
            if (count($lines) >= 100) {
                break;
            }
        }

        return $lines ?: $this->buildProductSection(100);
    }

    /**
     * Aggregate sales directly on the order tables, limited by the configured
     * lookback window, rather than joining the catalog onto sales_order_item.
     *
     * @return int[]
     */
    private function getBestSellerProductIds(): array
    {
        $connection = $this->resourceConnection->getConnection('sales');

        $select = $connection->select()
            ->from(
                ['soi' => $this->resourceConnection->getTableName('sales_order_item', 'sales')],
                ['product_id']
            )
            ->where('soi.store_id IN (?)', array_map('intval', $this->storeManager->getWebsite()->getStoreIds()))
            ->where('soi.parent_item_id IS NULL')
            ->where('soi.product_id IS NOT NULL')
            ->group('soi.product_id')
            ->order(new \Zend_Db_Expr('SUM(soi.qty_ordered) DESC'))
            // Over-fetch to allow for disabled / not-visible products being filtered out
            ->limit(300);

        $days = $this->config->getBestSellersDays();
        if ($days > 0) {
            $select->join(
                ['so' => $this->resourceConnection->getTableName('sales_order', 'sales')],
                'so.entity_id = soi.order_id',
                []
            )->where('so.created_at >= ?', gmdate('Y-m-d H:i:s', time() - $days * 86400));
        }

        return array_map('intval', $connection->fetchCol($select));
    }

    private function buildProductSection(int $pageSize = 500): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'status', 'visibility'])
            ->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                ProductVisibility::VISIBILITY_IN_CATALOG,
                ProductVisibility::VISIBILITY_IN_SEARCH,
                ProductVisibility::VISIBILITY_BOTH,
            ]])
            ->setStoreId($storeId)
            ->addUrlRewrite();

        if ($pageSize > 0) {
            $collection->setPageSize($pageSize);
        }

        $lines = [];
        foreach ($collection as $product) {
            $url = $product->getProductUrl();
            $name = $product->getName();
            if ($url && $name) {
                $lines[] = "- [{$name}]({$url})";
            }
        }

        return $lines;
    }
}
