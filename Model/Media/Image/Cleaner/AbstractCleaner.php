<?php
/**
 * 
 */
namespace FishPig\Util\Model\Media\Image\Cleaner;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCleaner implements ImageCleanerInterface
{
    /**
     * 
     */
    protected function recursiveScanDir(string $dir, callable $callback)
    {
        if (!($dh = opendir($dir))) {
            throw new \RuntimeException(sprintf('Unable to open directory: %s', $dir));
        }

        while (($item = readdir($dh)) !== false) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $file = $dir . '/' . $item;

            if (is_dir($file)) {
                $this->recursiveScanDir($file, $callback);
            } else {
                $callback($file);
            }
        }
    }

    /**
     * 
     */
    protected function keepFile(InputInterface $input, OutputInterface $output, string $file): void
    {
        if ($output->isVerbose()) {
            $output->writeLn(' - ' . str_replace(BP . '/pub/', '', $file));
        }
    }

    /**
     * 
     */
    protected function removeFile(InputInterface $input, OutputInterface $output, string $file): void
    {
        $output->writeLn('* ' . str_replace(BP . '/pub/', '', $file));

        if (!$input->getOption(ImageCleanerInterface::DRY_RUN)) {
            if (!unlink($file)) {
                throw new \RuntimeException(sprintf('Unable to delete file: %s', $file));
            }
        }
    }

    /**
     * 
     */
    protected function moveFile(InputInterface $input, OutputInterface $output, string $file, string $targetFile): void
    {
        $output->writeLn('* ' . str_replace(BP . '/pub/', '', $file));

        if (!$input->getOption(ImageCleanerInterface::DRY_RUN)) {
            if (!rename($file, $targetFile)) {
                throw new \RuntimeException(sprintf('Unable to move file: %s to %s', $file, $targetFile));
            }
        }
    }
}
