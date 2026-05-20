<?php
declare(strict_types=1);

namespace Limely\Crawly\Controller\AgentsMd;

use Limely\Crawly\Model\AgentsMd\Generator;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly ResultFactory $resultFactory,
        private readonly Generator $generator,
    ) {}

    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $result->setHeader('Cache-Control', 'public, max-age=3600');
        $result->setContents($this->generator->generate());

        return $result;
    }
}
