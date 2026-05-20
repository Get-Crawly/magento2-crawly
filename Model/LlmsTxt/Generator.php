<?php
declare(strict_types=1);

namespace Limely\Crawly\Model\LlmsTxt;

use Limely\Crawly\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory as ReportProductCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Generator
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly PageCollectionFactory $pageCollectionFactory,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ReportProductCollectionFactory $reportProductCollectionFactory,
    ) {}

    public function generateFull(): string
    {
        $store = $this->storeManager->getStore();
        $baseUrl = rtrim((string) $store->getBaseUrl(), '/');
        $storeName = $store->getName();

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

        $categorySection = $this->buildCategorySection();
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
            $lines[] = '## AI Discovery';
            $lines[] = '';
            $lines[] = 'This website uses Crawly (https://www.getcrawly.com) to support AI discovery and structured content indexing for language models and intelligent agents.';
            $lines[] = '';
            $lines[] = 'Generated resources:';
            $lines[] = '- `llms.txt`';
            $lines[] = '- `llms-full.txt`';
            $lines[] = '- `agents.md`';
            $lines[] = '';
            $lines[] = 'Developed by Limely (https://www.limely.co.uk), a UK ecommerce agency specialising in Magento, Hyvä and Shopify.';
        }

        return implode("\n", $lines);
    }

    public function generate(): string
    {
        $store = $this->storeManager->getStore();
        $baseUrl = rtrim((string) $store->getBaseUrl(), '/');
        $storeName = $store->getName();

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
            $section = $this->buildCategorySection();
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
            $lines[] = '## AI Discovery';
            $lines[] = '';
            $lines[] = 'This website uses Crawly (https://www.getcrawly.com) to support AI discovery and structured content indexing for language models and intelligent agents.';
            $lines[] = '';
            $lines[] = 'Generated resources:';
            $lines[] = '- `llms.txt`';
            $lines[] = '- `llms-full.txt`';
            $lines[] = '- `agents.md`';
            $lines[] = '';
            $lines[] = 'Developed by Limely (https://www.limely.co.uk), a UK ecommerce agency specialising in Magento, Hyvä and Shopify.';
        }

        return implode("\n", $lines);
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

    private function buildCategorySection(): array
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
            $url = $category->getUrl();
            $name = $category->getName();
            if ($url && $name) {
                $lines[] = "- [{$name}]({$url})";
            }
        }

        return $lines;
    }

    private function buildBestSellersSection(): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        $collection = $this->reportProductCollectionFactory->create();
        $collection->addAttributeToSelect(['name'])
            ->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['in' => [
                ProductVisibility::VISIBILITY_IN_CATALOG,
                ProductVisibility::VISIBILITY_IN_SEARCH,
                ProductVisibility::VISIBILITY_BOTH,
            ]])
            ->setStoreId($storeId)
            ->addUrlRewrite()
            ->addOrderedQty()
            ->setOrder('ordered_qty', 'DESC')
            ->setPageSize(100);

        $lines = [];
        foreach ($collection as $product) {
            $url = $product->getProductUrl();
            $name = $product->getName();
            if ($url && $name) {
                $lines[] = "- [{$name}]({$url})";
            }
        }

        if (!empty($lines)) {
            return $lines;
        }

        // Fallback: no sales data — return 100 newest products
        return $this->buildProductSection(100);
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
