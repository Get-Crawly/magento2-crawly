<?php
declare(strict_types=1);

namespace Limely\Crawly\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
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
    private const XML_PATH_SITEMAP_PATH             = 'sitemap/generate/sitemap_path';
    private const XML_PATH_SITEMAP_FILENAME         = 'sitemap/generate/sitemap_filename';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ThemeProviderInterface $themeProvider,
        private readonly Filesystem $filesystem,
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
        while ($theme) {
            if (str_starts_with((string) $theme->getCode(), 'Hyva/')) {
                return true;
            }
            $theme = $theme->getParentTheme();
        }

        return false;
    }

    public function getSitemapUrl(string $baseUrl): ?string
    {
        $path     = (string) $this->scopeConfig->getValue(self::XML_PATH_SITEMAP_PATH, ScopeInterface::SCOPE_STORE);
        $filename = (string) $this->scopeConfig->getValue(self::XML_PATH_SITEMAP_FILENAME, ScopeInterface::SCOPE_STORE);

        if (!$filename) {
            return null;
        }

        $relativePath = ltrim(rtrim($path, '/') . '/' . $filename, '/');

        try {
            $pubDir = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
            if (!$pubDir->isExist($relativePath)) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . $relativePath;
    }
}
