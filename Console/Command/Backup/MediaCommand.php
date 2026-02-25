<?php
/**
 * 
 */
namespace FishPig\Util\Console\Command\Backup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MediaCommand extends AbstractBackupCommand
{
    /**
     * 
     */
    public function __construct(
        private array $ignoredBackupPaths = [],
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * 
     */
    protected function configure()
    {
        $this->setName('backup:media');
        return parent::configure();
    }

    /**
     * 
     */
    protected function _execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFile = $this->getOutputFile();
        $mediaPath = $this->getMediaPath();

        $commands = [
            sprintf('cd %s', $mediaPath),
            $this->getBackupCommand($output),
            'cd - > /dev/null'
        ];

        $command = implode(" && \\\n", $commands);

        if ($output->isVerbose()) {
            $output->write("** Media Backup **\n\n" . $command . "\n\n");
        }
        
        passthru($command);
        
        if (!is_file($outputFile)) {
            throw new \RuntimeException(sprintf(
                'Backup failed! File "%s" does not exist',
                str_replace($this->getBasePath(), '', $outputFile)
            ));
        } elseif (!filesize($outputFile)) {
            throw new \RuntimeException(sprintf(
                'Backup failed! File "%s" exists but is empty',
                str_replace($this->getBasePath(), '', $outputFile)
            ));
        }

        $output->write($outputFile);

        return Command::SUCCESS;
    }

    /**
     * 
     */
    private function getBackupCommand($output): string
    {
        $backupFile = $this->getOutputFile();
        $backupCmd = [
            'zip',
            $output->isVerbose() ? '-v' : '-q',
            '-r',
            $backupFile
        ];

        foreach ($this->getIgnoredPaths() as $input) {
            $backupCmd[] = '--exclude=' . escapeshellarg($input);
        }

        $backupCmd[] = '.';

        return implode(' ', $backupCmd);
    }

    /**
     * 
     */
    private function getIgnoredPaths(): array
    {
        return array_values(
            array_unique(
                array_filter(
                    $this->ignoredBackupPaths
                )
            )
        );
    }

    /**
     * 
     */
    protected function getOutputFilename(): string
    {
        return sprintf(
            'media-%s.zip',
            date('Y-m-d-H-i-s')
        );
    }
}