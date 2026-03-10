<?php
/**
 * 
 */
namespace FishPig\Util\Controller\OPcache\Action;

class Status extends AbstractAction
{
    /**
     *
     */
    public function execute()
    {
        $status = opcache_get_status(false);

        $this->sendJsonResponse([
            'opcache' => [
                'enabled' => $status['opcache_enabled'],
                'cache_full' => $status['cache_full'],
                'restart_pending' => $status['restart_pending'],
                'restart_in_progress' => $status['restart_in_progress']
            ],
            'statistics' => [
                'hits' => $status['opcache_statistics']['hits'],
                'misses' => $status['opcache_statistics']['misses'],
                'opcache_hit_rate' => $status['opcache_statistics']['opcache_hit_rate']
            ]
        ]);
    }
}