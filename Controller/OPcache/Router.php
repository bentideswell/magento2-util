<?php
/**
 *
 */
namespace FishPig\Util\Controller\OPcache;

class Router
{
    /**
     *
     */
    public function __construct(
        private \FishPig\Util\Model\OPcache\Config $config,
        private array $params
    ) {}

    /**
     *
     */
    public function match()
    {
        if ($this->config->getAuthKey() !== $this->getAuthKey()) {
            return null;
        }

        $expectedRoutePattern = sprintf(
            '/\/%s\/(status|reset)$/',
            preg_quote($this->config->getRoutePath(), '/')
        );

        if (!preg_match($expectedRoutePattern, $this->getRequestUri(), $matches)) {
            return null;
        }

        if ($matches[1] === 'reset' && $this->getRequestMethod() === 'POST') {
            $actionClass = Action\Reset::class;
        } elseif ($matches[1] === 'status') {
            $actionClass = Action\Status::class;
        } else {
            return null;
        }

        return new $actionClass(
            $this->config,
            \FishPig\Util\Model\OPcache\Logger::getInstance()
        );
    }

    /**
     * 
     */
    private function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /**
     * 
     */
    private function getRequestMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    }

    /**
     * 
     */
    private function getAuthKey(): ?int
    {
        return (int)($this->params['HTTP_X_AUTH_KEY'] ?? 0) ?: null;
    }
}
