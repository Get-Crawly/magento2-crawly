# Limely_Crawly — Module Roadmap

## Current
- **llms.txt Generator** — dynamic, per-store-view llms.txt served at `/llms.txt`

---

## Planned

### AI / LLM Visibility
- **llms-full.txt generation** — optional full-text version of each page for LLMs that want richer context, served at `/llms-full.txt`
- **Structured data injector** — auto-add JSON-LD (Organization, BreadcrumbList, Product) to pages that are missing it
- **AI meta description audit** — admin grid flagging products and categories with missing or thin meta descriptions

### Technical SEO
- **Robots.txt manager** — edit and validate robots.txt from within Magento admin with a live preview
- **Canonical tag auditor** — surface pages with missing, self-referencing, or conflicting canonicals in a report grid
- **Hreflang generator** — auto-generate hreflang tags across store views for multi-language and multi-region setups
- **Security headers checker** — report on missing HSTS, CSP, X-Frame-Options, Referrer-Policy per store

### Crawlability
- **XML sitemap enhancements** — image sitemaps, priority rules per category depth, exclude out-of-stock products
- **Redirect manager** — clean admin UI for managing 301/302 redirects without server config access
- **Broken link scanner** — scheduled cron that checks internal links and emails a report to the store admin

### Content
- **Open Graph audit** — filterable admin grid flagging products and categories missing og:image, og:title, or og:description
- **Thin content report** — flag pages below a configurable word count threshold

---

## Notes
- Hreflang generator and redirect manager are likely the highest-value items for Magento agencies
- llms-full.txt is low effort and a natural v2 of the existing llms.txt feature
