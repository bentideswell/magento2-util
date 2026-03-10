<?php
/**
 * 
 */
namespace FishPig\Util\Model\Media\Image\Cleaner;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FishPigWebp extends AbstractCleaner
{
    /**
     * 
     */
    public function process(InputInterface $input, OutputInterface $output): void
    {
        $basePaths = [
            'pub/media/fishpig/webp/catalog' => 'pub/media/catalog',
            'pub/media/fishpig/webp/wysiwyg' => 'pub/media/wysiwyg',
            'pub/media/fishpig/webp/static' => 'pub/static'
        ];

        foreach ($basePaths as $basePath => $originalBasePath) {
            $this->recursiveScanDir($basePath, function ($file) use ($input, $output, $basePath, $originalBasePath) {
                $originalFileWithoutExtension = preg_replace(
                    '/' . preg_quote($basePath, '/') . '\/(.*)\.webp/',
                    $originalBasePath . '/$1',
                    $file
                );

                foreach (['.jpg', '.jpeg', '.png'] as $extension) {
                    $originalFile = $originalFileWithoutExtension . $extension;

                    if (is_file($originalFile)) {
                        $this->keepFile($input, $output, $file);
                        return;
                    }
                }
    

                $this->removeFile($input, $output, $file);
            });
        }
    }
}
