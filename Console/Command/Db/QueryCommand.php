<?php
/**
 * 
 */
namespace FishPig\Util\Console\Command\Db;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class QueryCommand extends \FishPig\Util\Console\Command\AbstractCommand
{
    /**
     * 
     */
    private const QUERY = 'query';
    private const DELETE = 'delete';

    /**
     * 
     */
    private $connection;

    /**
     * 
     */
    public function __construct(
        private \Magento\Framework\App\ResourceConnection $resourceConnection,
        ?string $name = null
    ) {
        $this->connection = $this->resourceConnection->getConnection();

        parent::__construct($name);
    }

    /**
     * 
     */
    protected function configure()
    {
        $this->setName('db:query');
        $this->addArgument(
            self::QUERY,
            InputArgument::REQUIRED,
            'The SQL query to run'
        );
        $this->addOption(
            self::DELETE,
            null,
            InputOption::VALUE_NONE,
            'Extra param required to allow delete SQL query'
        );

        return parent::configure();
    }

    /**
     * 
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $query = $input->getArgument(self::QUERY);

        $queryType = $this->parseQueryType($query);

        $io->section('** ' . ucwords($queryType) . ' Query **');
        $io->text($query);
        $io->newLine();
        
        try {
            if (in_array($queryType, ['SELECT', 'SHOW'])) {
                if ($results = $this->connection->fetchAll($query)) {
                    $io->table(array_keys($results[0]), $results);
                }

                $io->text(sprintf('%d row%s', count($results), count($results) === 1 ? '' : 's'));
            } elseif ($queryType === 'DELETE') {
                if (!$input->getOption(self::DELETE)) {
                    throw new \RuntimeException(
                        sprintf(
                            'DELETE queries require confirmation via the --%s option',
                            self::DELETE
                        )
                    );
                }

                $this->connection->query($query);
            } elseif (in_array($queryType, ['INSERT', 'UPDATE'])) {
                $this->connection->query($query);
            } else {
                throw new \InvalidArgumentException('Unsupported query type: ' . $queryType);
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->text('<fg=red>** Exception **</>');
            $io->newLine();
            $io->text('<error>' . $e->getMessage() . '</error>');

            if ($output->isVerbose()) {
                $io->newLine();
                $io->text('<fg=red>** Error Trace **</>');
                $io->newLine();
                $io->text(str_replace(BP . '/', '', $e->getTraceAsString()));
            }
            
            return Command::FAILURE;
        }
    }

    /**
     * 
     */
    private function parseQueryType(string $query): string
    {
        $trimmedQuery = ltrim($query);
        $firstWord = strtoupper(strtok($trimmedQuery, ' '));

        return $firstWord;
    }
}