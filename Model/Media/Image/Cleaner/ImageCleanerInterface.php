<?php
/**
 * 
 */
namespace FishPig\Util\Model\Media\Image\Cleaner;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ImageCleanerInterface
{
    /**
     * 
     */
    public const DRY_RUN = 'dry-run';

    /**
     * 
     */
    public function process(InputInterface $input, OutputInterface $output): void;
}
