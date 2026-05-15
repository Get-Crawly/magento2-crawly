<?php
declare(strict_types=1);

namespace Limely\Crawly\Router;

use Limely\Crawly\Model\Config;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;

class LlmsTxt implements RouterInterface
{
    public function __construct(
        private readonly ActionFactory $actionFactory,
        private readonly Config $config,
    ) {}

    public function match(RequestInterface $request): ?ActionInterface
    {
        $path = trim($request->getPathInfo(), '/');

        if ($path !== 'llms.txt') {
            return null;
        }

        if (!$this->config->isLlmsTxtEnabled()) {
            return null;
        }

        $request->setModuleName('limely_crawly')
            ->setControllerName('llmstxt')
            ->setActionName('index');

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class
        );
    }
}
