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
        $this->sendTextResponse(
            opcache_get_status() ? 'OPcache is enabled' : 'OPcache is disabled'
        );
    }
}