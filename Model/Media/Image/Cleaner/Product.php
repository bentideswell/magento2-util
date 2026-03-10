<?php
/**
 * 
 */
namespace FishPig\Util\Model\Media\Image\Cleaner;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Product extends AbstractCleaner
{
    /**
     * 
     */
    private $connection;

    /**
     * 
     */
    public function __construct(
        private \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * 
     */
    public function process(InputInterface $input, OutputInterface $output): void
    {
        $basePath = BP . '/pub/media/catalog/product';
        $holdingPath = BP . '/pub/media/catalog/product-holding';
        $dryRun = $input->getOption(ImageCleanerInterface::DRY_RUN);

        $this->recursiveScanDir(
            $basePath,
            function ($file) use ($input, $output, $basePath, $holdingPath, $dryRun) {
                if (strpos($file, '/cache/') !== false) {
                    return;
                }

                $relativeFile = '/' . str_replace($basePath . '/', '', $file);

                if (!$this->isImageInUse($relativeFile)) {
                    $holdingFile = str_replace($basePath, $holdingPath, $file);
                    $holdingFileParentPath = dirname($holdingFile);

                    if (!is_dir($holdingFileParentPath) && !mkdir($holdingFileParentPath, 0755, true)) {
                        throw new \RuntimeException(sprintf('Unable to create directory: %s', $holdingFileParentPath));
                    }
                    
                    if (!$dryRun) {
                        if (!rename($file, $holdingFile)) {
                            throw new \RuntimeException(sprintf('Unable to move file: %s to %s', $file, $holdingFile));
                        }
                    }

                    $output->writeln($file);
                }
            }
        );
    }

    /**
     * 
     */
    private function isImageInUse(string $relativeFile): bool
    {
        return $this->isImageInMediaGalleryTable($relativeFile) || $this->isImageInProductVarcharTable($relativeFile);
    }

    /**
     * 
     */
    private function isImageInMediaGalleryTable(string $relativeFile): bool
    {
        return (bool)$this->connection->fetchOne(
            $this->connection->select()
                ->from($this->resourceConnection->getTableName('catalog_product_entity_media_gallery'), ['value_id'])
                ->where('value = ?', $relativeFile)
                ->limit(1)
        );
    }

    /**
     * 
     */
    private function isImageInProductVarcharTable(string $relativeFile): bool
    {
        return (bool)$this->connection->fetchOne(
            $this->connection->select()
                ->from($this->resourceConnection->getTableName('catalog_product_entity_varchar'), ['value_id'])
                ->where('value = ?', $relativeFile)
                ->limit(1)
        );
    }
}
