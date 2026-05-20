<?php
declare(strict_types=1);

namespace Limely\Crawly\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED                  = 'limely_crawly/llmstxt/enabled';
    private const XML_PATH_CMS_PAGES                = 'limely_crawly/llmstxt/include_cms_pages';
    private const XML_PATH_CATEGORIES               = 'limely_crawly/llmstxt/include_categories';
    private const XML_PATH_PRODUCTS                 = 'limely_crawly/llmstxt/include_products';
    private const XML_PATH_POWERED_BY               = 'limely_crawly/llmstxt/powered_by';
    private const XML_PATH_CUSTOM_INTRO             = 'limely_crawly/llmstxt/custom_intro';
    private const XML_PATH_AGENTS_MD_ENABLED        = 'limely_crawly/agentsmd/enabled';
    private const XML_PATH_AGENTS_MD_CUSTOM_INTRO   = 'limely_crawly/agentsmd/custom_intro';
    private const XML_PATH_AGENTS_MD_CUSTOM_CONTENT = 'limely_crawly/agentsmd/custom_content';
    private const XML_PATH_ANON_REST_ALLOWED        = 'webapi/webapiSecurity/allow_insecure';
    private const XML_PATH_DESIGN_THEME             = 'design/theme/theme_id';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ThemeProviderInterface $themeProvider,
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

    public function isAgentsMdEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_AGENTS_MD_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getAgentsMdCustomIntro(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_AGENTS_MD_CUSTOM_INTRO, ScopeInterface::SCOPE_STORE);
    }

    public function getAgentsMdCustomContent(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_AGENTS_MD_CUSTOM_CONTENT, ScopeInterface::SCOPE_STORE);
    }

    public function isAnonymousRestAllowed(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANON_REST_ALLOWED, ScopeInterface::SCOPE_STORE);
    }

    public function isHyvaTheme(): bool
    {
        $themeId = $this->scopeConfig->getValue(self::XML_PATH_DESIGN_THEME, ScopeInterface::SCOPE_STORE);
        if (!$themeId) {
            return false;
        }

        $theme = $this->themeProvider->getThemeById((int) $themeId);

        return $theme && str_starts_with((string) $theme->getCode(), 'Hyva/');
    }
}
