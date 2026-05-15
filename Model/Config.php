<?php
declare(strict_types=1);

namespace Limely\Crawly\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED      = 'limely_crawly/llmstxt/enabled';
    private const XML_PATH_CMS_PAGES    = 'limely_crawly/llmstxt/include_cms_pages';
    private const XML_PATH_CATEGORIES   = 'limely_crawly/llmstxt/include_categories';
    private const XML_PATH_PRODUCTS     = 'limely_crawly/llmstxt/include_products';
    private const XML_PATH_POWERED_BY   = 'limely_crawly/llmstxt/powered_by';
    private const XML_PATH_CUSTOM_INTRO = 'limely_crawly/llmstxt/custom_intro';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function isLlmsTxtEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function includeCmsPages(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CMS_PAGES, ScopeInterface::SCOPE_STORE);
    }

    public function includeCategories(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATEGORIES, ScopeInterface::SCOPE_STORE);
    }

    public function includeProducts(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PRODUCTS, ScopeInterface::SCOPE_STORE);
    }

    public function showPoweredBy(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_POWERED_BY, ScopeInterface::SCOPE_STORE);
    }

    public function getCustomIntro(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_INTRO, ScopeInterface::SCOPE_STORE);
    }
}
