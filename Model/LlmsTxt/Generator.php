<?php
declare(strict_types=1);

namespace Limely\Crawly\Model\LlmsTxt;

use Limely\Crawly\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;

class Generator
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
    ) {}

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
            $lines[] = '## Crawling & AI Discovery';
            $lines[] = '';
            $lines[] = 'This website uses Crawly (https://www.getcrawly.com) to improve technical visibility';
            $lines[] = 'for search engines, AI assistants, and LLM-powered discovery systems.';
            $lines[] = '';
            $lines[] = 'Designed for modern indexing and intelligent web crawling. Built by Limely (https://www.limely.co.uk), a leading ecommerce agency specialising in Magento, Hyvä and Shopify.';
        }

        return implode("\n", $lines);
    }

    private function buildCmsSection(string $baseUrl): array
    {
        $skipIdentifiers = ['no-route', 'home', '404', 'no_route', 'enable-cookies', 'privacy-policy-cookie-restriction-mode'];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();

        $lines = [];
        foreach ($pages as $page) {
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

    private function buildProductSection(): array
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
            ->addUrlRewrite()
            ->setPageSize(500);

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
