<?php
/**
 * 
 */
namespace FishPig\Util\Console\Command;

use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * 
     */
    protected function getBasePath(): string
    {
        return BP;
    }

    /**
     * 
     */
    protected function getMediaPath(): string
    {
        return $this->getBasePath() . '/pub/media';
    }
}