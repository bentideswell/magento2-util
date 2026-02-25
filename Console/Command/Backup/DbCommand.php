<?php
/**
 * 
 */
namespace FishPig\Util\Console\Command\Backup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbCommand extends AbstractBackupCommand
{
    /**
     * 
     */
    private ?array $dbConfig = null;

    /**
     * 
     */
    public function __construct(
        private $tablesWithSchemaToExcludeFromBackup = [],
        private $tablesWithDataToExcludeFromBackup = [],
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * 
     */
    protected function configure()
    {
        $this->setName('backup:db');
        return parent::configure();
    }

    /**
     * 
     */
    protected function _execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFile = $this->getOutputFile();
        $compressedOutputFile = dirname($outputFile) . '/' . $this->getFinalOutputFilename();
        $finalOutputFile = $compressedOutputFile;

        $commands = [
            $this->registerNewCommand(
                'Touch output file',
                sprintf('touch %s', $outputFile),
                fn() => is_file($outputFile)
            ),
            $this->registerNewCommand(
                'Backup schema',
                $this->getBackupSchemaCommand(),
                fn() => filesize($outputFile) > 0
            ),
            $this->registerNewCommand(
                'Remove DEFINER',
                function() use ($outputFile) {
                    $this->removeDefiner($outputFile);
                },
                fn() => is_file($outputFile)
            ),
            $this->registerNewCommand(
                'Backup data',
                $this->getBackupDataCommand(),
                fn() => filesize($outputFile) > 0
            ),
            $this->registerNewCommand(
                'Compress output',
                sprintf('gzip %s', $outputFile),
                fn() => is_file($compressedOutputFile) && filesize($compressedOutputFile) > 0
            )
        ];

        $isOutputVerbose = $output->isVerbose();

        foreach ($commands as $command) {
            if ($isOutputVerbose) {
                $output->write(sprintf(
                    "** %s **\n\n",
                    $command['description']
                ));
            }

            if (is_callable($command['command'])) {
                $command['command']();
            } else {
                if ($isOutputVerbose) {
                    $output->write(sprintf(
                        "%s\n\n",
                        $command['command']
                    ));
                }

                exec($command['command'], $response, $exitCode);
            }

            if (!$command['validationCallback']()) {
                if ($isOutputVerbose) {
                    $output->write("Validation failed!\n\n");
                }

                throw new \RuntimeException(sprintf(
                    'Validation failed during step "%s"',
                    $command['description']
                ));
            }

            clearstatcache($outputFile);
            clearstatcache($finalOutputFile);
        }
    
        if (!is_file($finalOutputFile) || filesize($finalOutputFile) === 0) {
            throw new \RuntimeException(sprintf(
                "The final output file is missing or empty.\n\nFile: %s\n\n",
                $finalOutputFile
            ));
        }

        if ($isOutputVerbose) {
            $output->write(" ** Backup complete! **\n\n");
            $output->write(sprintf("Output file: %s\n\n", $finalOutputFile));
        } else {
            $output->write($finalOutputFile);
        }

        return Command::SUCCESS;
    }


    /**
     * 
     */
    private function getConnectionConfig(): array
    {
        if ($this->dbConfig === null) {
            $env = include BP . '/app/etc/env.php';

            $this->dbConfig = $env['db']['connection']['default'];
            $this->dbConfig['table_prefix'] = $this->dbConfig['dbname'] . '.' . $env['db']['table_prefix'];
        }

        return $this->dbConfig;
    }

    /**
     * 
     */
    protected function getOutputFilename(): string
    {
        return sprintf(
            'db-%s-%s.sql',
            $this->getConnectionConfig()['dbname'],
            date('Y-m-d-H-i-s')
        );
    }

    private function getIgnoredTableList(): array
    {
        return $this->cleanTableList(
            $this->tablesWithSchemaToExcludeFromBackup
        );
    }

    private function getIgnoredTableDataList(): array
    {
        return $this->cleanTableList(
            array_merge(
                $this->getIgnoredTableList(),
                $this->tablesWithDataToExcludeFromBackup
            )
        );
    }

    /**
     * 
     */
    private function cleanTableList(array $tables): array
    {
        return array_values(array_unique(array_filter($tables)));
    }

    private function getBackupSchemaCommand(): string
    {
        return $this->createBackupCommand(
            '--no-data ' . $this->convertTableListIntoCommand($this->getIgnoredTableList())
        );
    }

    /**
     * 
     */
    private function getBackupDataCommand(): string
    {
        return $this->createBackupCommand(
            '--no-create-info ' . $this->convertTableListIntoCommand($this->getIgnoredTableDataList())
        );
    }

    /**
     * 
     */
    private function createBackupCommand(string $command = ''): string
    {
        $dbConfig = $this->getConnectionConfig();

        return sprintf(
            'mysqldump --host=%s -u%s -p\'%s\' --skip-triggers --no-tablespaces %s %s >> %s',
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $command,
            $this->getConnectionConfig()['dbname'],
            $this->getOutputFile()
        );
    }

    /**
     * 
     */
    private function convertTableListIntoCommand($tables): string
    {
        $dbConfig = $this->getConnectionConfig();

        return implode(
            ' ',
            array_map(
                fn($t) => '--ignore-table=' . $dbConfig['table_prefix'] . $t,
                $tables
            )
        );
    }

    /**
     * 
     */
    private function registerNewCommand(
        string $description,
        mixed $command,
        callable $validationCallback
    ): array {
        return [
            'description' => $description,
            'command' => $command,
            'validationCallback' => $validationCallback
        ];
    }

    private function removeDefiner(string $outputFile): void
    {
        $tempFile = $outputFile . '.tmp';
        $sourceFile = fopen($outputFile, 'r');
        $targetFile = fopen($tempFile, 'w');
    
        while (($line = fgets($sourceFile)) !== false) {
            fwrite($targetFile, $this->removeDefinerLineCallback($line));
        }
    
        fclose($sourceFile);
        fclose($targetFile);
    
        unlink($outputFile);
        rename($tempFile, $outputFile);
    }

    /**
     * 
     */
    private function removeDefinerLineCallback(string $line): string
    {
        if (strpos($line, 'DEFINER=') !== false) {
            $line = preg_replace(
                '/DEFINER=`[^`]+`@`[^`]+`/U',
                "DEFINER=CURRENT_USER",
                $line
            );
        }

        return $line;
    }

    /**
     * 
     */
    protected function getFinalOutputFilename(): string
    {
        return $this->getOutputFilename() . '.gz';
    }
}