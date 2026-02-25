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
        if ($this->config->isOPcacheEnabled()) {
            $this->sendTextResponse(
                opcache_reset() ? 'OPcache reset successfully' : 'OPcache reset failed'
            );
        } else {
            $this->sendTextResponse('OPcache is not enabled');
        }
    }
}