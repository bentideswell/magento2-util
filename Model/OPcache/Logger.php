<?php
/**
 *
 */
namespace FishPig\Util\Model\OPcache;

class Logger
{
    /**
     * 
     */
    private static $instance = null;

    /**
     * 
     */
    protected function __construct() {}

    /**
     * 
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     *
     */
    public function log(string $message): void
    {
        $this->write(
            implode(
                '  ',
                [
                    date('Y-m-d H:i:s'),
                    $this->getRemoteAddress(),
                    $message
                ]
            )
        );
    }

    /**
     * 
     */
    private function write(string $message): void
    {
        $logFile = BP . '/var/log/fishpig-opcache.log';

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * 
     */
    private function getRemoteAddress(): ?string
    {
        $keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];
    
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
    
        return null;
    }
}
