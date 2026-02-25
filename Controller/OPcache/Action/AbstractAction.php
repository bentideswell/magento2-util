<?php
/**
 * 
 */
namespace FishPig\Util\Controller\OPcache\Action;

abstract class AbstractAction implements \Magento\Framework\App\ActionInterface
{
    /**
     *
     */
    public function __construct(
        protected \FishPig\Util\Model\OPcache\Config $config,
        protected \FishPig\Util\Model\OPcache\Logger $logger
    ) {}

    /**
     * 
     */
    protected function sendJsonResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 
     */
    protected function sendTextResponse(string $body): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo $body;
        exit;
    }
}