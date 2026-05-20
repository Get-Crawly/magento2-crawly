<?php
declare(strict_types=1);

namespace Limely\Crawly\Router;

use Limely\Crawly\Controller\AgentsMd\Index as AgentsMdController;
use Limely\Crawly\Controller\LlmsTxt\Full as LlmsTxtFullController;
use Limely\Crawly\Controller\LlmsTxt\Index as LlmsTxtController;
use Limely\Crawly\Model\Config;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;

class Crawly implements RouterInterface
{
    public function __construct(
        private readonly ActionFactory $actionFactory,
        private readonly Config $config,
    ) {}

    public function match(RequestInterface $request): ?ActionInterface
    {
        $path = trim($request->getPathInfo(), '/');

        if ($path === 'llms.txt' || $path === 'llms-full.txt') {
            if (!$this->config->isLlmsTxtEnabled()) {
                return null;
            }

            if ($path === 'llms.txt') {
                return $this->actionFactory->create(LlmsTxtController::class);
            }

            return $this->actionFactory->create(LlmsTxtFullController::class);
        }

        if ($path === 'agents.md') {
            if (!$this->config->isAgentsMdEnabled()) {
                return null;
            }

            return $this->actionFactory->create(AgentsMdController::class);
        }

        return null;
    }
}
