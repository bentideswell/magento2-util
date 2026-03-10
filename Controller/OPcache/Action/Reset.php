<?php
/**
 * 
 */
namespace FishPig\Util\Controller\OPcache\Action;

class Reset extends AbstractAction
{
    /**
     *
     */
    public function execute()
    {
        $responseData = [];

        if (!$this->config->isOPcacheInstalled()) {
            $responseData['error'] = 'OPcache is not installed';
        } else {
            $responseData['opcache'] = [
                'enabled' => $this->config->isOPcacheEnabled(),
                'reset' => opcache_reset(),
                'restart_pending' => opcache_get_status(false)['restart_pending'] ?? null,
                'restart_in_progress' => opcache_get_status(false)['restart_in_progress'] ?? null
            ];
        }

        $this->sendJsonResponse($responseData);
    }
}