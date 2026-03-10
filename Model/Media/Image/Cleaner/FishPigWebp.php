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
        $basePath = BP . '/pub/media/fishpig/webp';
        $dryRun = $input->getOption(ImageCleanerInterface::DRY_RUN);

        $this->recursiveScanDir(
            $basePath,
            function ($file) use ($input, $output, $basePath, $dryRun) {
                $originalFileWithoutExtension = preg_replace('/fishpig\/webp\/(.*)\.webp/', '$1', $file);

                foreach (['.jpg', '.jpeg', '.png'] as $extension) {
                    $originalFile = $originalFileWithoutExtension . $extension;
                
                    if (is_file($originalFile)) {
                        return;
                    }
                }

                $output->writeLn($file);

                if (!$dryRun) {
                    $this->unlink($file);
                }
            }
        );
    }
}
