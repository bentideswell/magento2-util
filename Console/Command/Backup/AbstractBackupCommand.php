<?php
/**
 * 
 */
namespace FishPig\Util\Console\Command\Backup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractBackupCommand extends \FishPig\Util\Console\Command\AbstractCommand
{
    /**
     * 
     */
    private const CLEAN = 'clean';

    /**
     * 
     */
    private ?string $outputFile = null;

    /**
     * 
     */
    abstract protected function _execute(InputInterface $input, OutputInterface $output): int;

    /**
     * 
     */
    abstract protected function getOutputFilename(): string;

    /**
     * 
     */
    public function __construct(
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * 
     */
    protected function configure()
    {
        $this->addOption(
            self::CLEAN,
            null,
            InputOption::VALUE_REQUIRED,
            'The number of days to keep existing backups.'
        );

        return parent::configure();
    }

    /**
     * 
     */
    final public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (($daysToKeepBackups = $input->getOption(self::CLEAN)) !== null) {
            $this->cleanExistingBackups($input, $output, (int)$daysToKeepBackups);
        }

        return $this->_execute($input, $output);
    }

    /**
     * 
     */
    protected function cleanExistingBackups(InputInterface $input, OutputInterface $output, int $daysToKeepBackups): void
    {
        $outputFile = $this->getFinalOutputFilename();
        $outputPath = dirname($this->getOutputFile());

        if (!($fileExtension = preg_replace('/^.*\./U', '.', basename($outputFile)))) {
            throw new \RuntimeException(sprintf(
                'Unable to determine file extension for "%s"',
                $outputFile
            ));
        }

        $cutOffTime = time() - ($daysToKeepBackups * 24 * 60 * 60);

        foreach (scandir($outputPath) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            } elseif (!preg_match('/' . preg_quote($fileExtension, '/') . '$/', $item)) {
                continue;
            }

            $file = $outputPath . '/' . $item;
            $mTime = filemtime($file);

            if ($mTime >= $cutOffTime) {
                continue;
            }

            if ($output->isVerbose()) {
                $output->writeLn('** Deleting:  ' . str_replace(BP . '/', '', $file) . ' **');
            }

            unlink($file);
        }
    }

    /**
     * 
     */
    protected function getOutputFile(): string
    {
        if ($this->outputFile !== null) {
            return $this->outputFile;
        }

        $outputPath = $this->getBasePath() . '/var/backups';
    
        if (!is_dir($outputPath) && !@mkdir($outputPath)) {
            throw new \RuntimeException(sprintf(
                'Unable to create directory at %s',
                $outputPath
            ));
        }
    
        if (!is_writable($outputPath)) {
            throw new \RuntimeException(sprintf(
                'Unable to write to path "%s"',
                $outputPath
            ));
        }

        return $this->outputFile = $outputPath . '/' . $this->getOutputFilename();
    }


    /**
     * 
     */
    protected function getFinalOutputFilename(): string
    {
        return $this->getOutputFilename();
    }
}