<?php
/**
 *
 */
namespace FishPig\Util\Model\OPcache;

class Config
{
    /**
     *
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * 
     */
    public function getRoutePath(): string
    {
        return 'fishpig-opcache';
    }

    /**
     * 
     */
    public function getAuthKey(): int
    {
        return date('j') * date('N');
    }

    /**
     * 
     */
    public function isOPcacheEnabled(): bool
    {
        return (int)(opcache_get_status()['opcache_enabled'] ?? 0) === 1;
    }
}
