<?php
/**
 * 
 */
namespace FishPig\Util\Model\Media\Image\Cleaner;

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
    protected function unlink(string $file): void
    {
        if (!unlink($file)) {
            throw new \RuntimeException(sprintf('Unable to delete file: %s', $file));
        }
    }
}
